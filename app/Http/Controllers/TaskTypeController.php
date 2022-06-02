<?php

namespace App\Http\Controllers;


use App\Models\TaskType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Roles;

class TaskTypeController extends Controller
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
        $typeQuery = TaskType::query();

        if($request->has('name')) {
            $typeQuery->where('name', 'like', filter($request->get('name')));
        }

        $count = $typeQuery->count();
        $types = $typeQuery->limit($request->get('limit'))->offset($request->get('offset'))->get();


        return response()->json(['data' => $types, 'total' => $count]);
    }
    public function store(Request $request){
        if(checkRole()==Roles::WORKER ){
            return permissionError();
        }
        $validator = Validator::make($request->all(), [
            'name'=>['required','string','unique:departaments'],

        ]);
        if ($validator->fails())
        {
            return validationError($validator->errors());
        }
        $model= new TaskType();
        $model->name=$request->name;
        $model->status=1;
        $model->save();

        return createSuccess($model);
    }
    public function update(Request $request)
    {
        if(checkRole()==Roles::WORKER ){
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
        $model=TaskType::find($request->id);
        $model->name=$request->name;
        $model->status=$request->status;

        $model->save();
        return updateSuccess($model);
    }
    public function tree(Request $request): JsonResponse
    {
        paginate($request, $limit, $offset);
        $typeQuery = TaskType::query();

        if($request->has('name')) {
            $typeQuery->where('name', 'like', filter($request->get('name')));
        }

        $count = $typeQuery->count();
        $types = $typeQuery->limit($request->get('limit'))->offset($request->get('offset'))->get();

        $data = simpleTree($types);
        return response()->json(['data' => $data, 'total' => $count]);
    }
    public function single($id){
        $model= TaskType::query()->find($id);

        if (!$model) {
            return notFoundError($id);
        }

        return response()->json($model);
    }
    public function delete($id){
        if(checkRole()==Roles::WORKER){
            return permissionError();
        }
        $model= TaskType::query()->find($id);

        if (!$model) {
            return notFoundError($id);
        }
        $delete=checkIfExist('Task','task_type',$id);
        if($delete==1){
            return notDeleteError();
        }
        TaskType::where('id', '=', $id)->delete();

        return deleted();

    }
    //
}
