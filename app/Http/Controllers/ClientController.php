<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Departament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Roles;

class ClientController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function all(Request $request): JsonResponse
    {
        paginate($request, $limit, $offset);
        $clientQuery = Client::query();

        if($request->has('name')) {
            $clientQuery->where('name', 'like', filter($request->get('name')));
        }

        $count = $clientQuery->count();
        $clients = $clientQuery->limit($request->get('limit'))->offset($request->get('offset'))->get();


        return response()->json(['data' => $clients, 'total' => $count]);
    }
    public function store(Request $request){
        if(checkRole()!=Roles::SYS_OWNER &&checkRole()!=Roles::COMPANY_OWNER){
            return permissionError();
        }
        $validator = Validator::make($request->all(), [
            'name'=>['required','string','unique:clients'],

        ]);
        if ($validator->fails())
        {
            return validationError($validator->errors());
        }
        $model= new Client();
        $model->name=$request->name;
        $model->status=1;
        $model->save();

        return createSuccess($model);
    }
    public function update(Request $request)
    {
        if(checkRole()!=Roles::SYS_OWNER &&checkRole()!=Roles::COMPANY_OWNER){
            return permissionError();
        }
        $validator = Validator::make($request->all(), [
            'name'=>['string',Rule::unique('clients')->ignore($request->id)],
            'status'=>['required','integer'],

        ]);

        if ($validator->fails())
        {
            return validationError($validator->errors());
        }
        $model=Client::find($request->id);
        $model->name=$request->name;
        $model->status=$request->status;

        $model->save();
        return updateSuccess($model);
    }
    public function tree(Request $request): JsonResponse
    {
        paginate($request, $limit, $offset);
        $clientQuery = Client::query();

        if($request->has('name')) {
            $clientQuery->where('name', 'like', filter($request->get('name')));
        }

        $count = $clientQuery->count();
        $clients = $clientQuery->limit($request->get('limit'))->offset($request->get('offset'))->get();

        $data = simpleTree($clients);
        return response()->json(['data' => $data, 'total' => $count]);
    }
    public function single($id){
        $model= Client::query()->find($id);

        if (!$model) {
            return notFoundError($id);
        }

        return response()->json($model);
    }
    public function delete($id){
        if(checkRole()!=Roles::SYS_OWNER &&checkRole()!=Roles::COMPANY_OWNER){
            return permissionError();
        }
        $model= Client::query()->find($id);

        if (!$model) {
            return notFoundError($id);
        }
        $delete=checkIfExist('Project','client_id',$id);
        if($delete==1){
            return notDeleteError();
        }
        Client::where('id', '=', $id)->delete();
        return deleted();

    }
    //
}
