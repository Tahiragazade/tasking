<?php

namespace App\Http\Controllers;

use App\Models\Departament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Roles;

class DepartamentController extends Controller
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
        $departamentQuery = Departament::query();

        if($request->has('name')) {
            $departamentQuery->where('name', 'like', filter($request->get('name')));
        }

        $count = $departamentQuery->count();
        $departaments = $departamentQuery->limit($request->get('limit'))->offset($request->get('offset'))->get();


        return response()->json(['data' => $departaments, 'total' => $count]);
    }
    public function store(Request $request){
        if(checkRole()!=Roles::SYS_OWNER &&checkRole()!=Roles::COMPANY_OWNER){
            return permissionError();
        }
        $validator = Validator::make($request->all(), [
            'name'=>['required','string','unique:departaments'],

        ]);
        if ($validator->fails())
        {
            return validationError($validator->errors());
        }
        $model= new Departament();
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
            'name'=>['string',Rule::unique('departaments')->ignore($request->id)],
            'status'=>['required','integer'],

        ]);

        if ($validator->fails())
        {
            return validationError($validator->errors());
        }
        $model=Departament::find($request->id);
        $model->name=$request->name;
        $model->status=$request->status;

        $model->save();
        return updateSuccess($model);
    }
    public function tree(Request $request): JsonResponse
    {
        paginate($request, $limit, $offset);
        $departamentQuery = Departament::query();

        if($request->has('name')) {
            $departamentQuery->where('name', 'like', filter($request->get('name')));
        }

        $count = $departamentQuery->count();
        $departaments = $departamentQuery->limit($request->get('limit'))->offset($request->get('offset'))->get();

        $data = simpleTree($departaments);
        return response()->json(['data' => $data, 'total' => $count]);
    }
    public function single($id){
        $model= Departament::query()->find($id);

        if (!$model) {
            return notFoundError($id);
        }

        return response()->json($model);
    }
    public function delete(){
        if(checkRole()!=Roles::SYS_OWNER &&checkRole()!=Roles::COMPANY_OWNER){
            return permissionError();
        }

    }
    //
}
