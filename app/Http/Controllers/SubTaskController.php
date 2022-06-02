<?php

namespace App\Http\Controllers;


use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\Workers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Roles;
use TaskStatus;

class SubTaskController extends Controller
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
        $subTaskQuery = SubTask::query();

//        if($request->has('name')) {
//            $subTaskQuery->where('name', 'like', filter($request->get('name')));
//        }

        $count = $subTaskQuery->count();
        $sub_tasks = $subTaskQuery->limit($request->get('limit'))->offset($request->get('offset'))->get();


        return response()->json(['data' => $sub_tasks, 'total' => $count]);
    }
    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'name'=>['required','string'],
            'task_id'=>['required','integer'],
            'note'=>['string'],

        ]);
        if ($validator->fails())
        {
            return validationError($validator->errors());
        }
        $model= new SubTask();
        $model->name=$request->name;
        $model->task_id=$request->task_id;
        $model->note=$request->note;
        $model->status=TaskStatus::TASK_WAITING;
        $model->created_by=Auth::id();

        $model->save();


        return createSuccess($model);
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>['required','string'],
            'task_id'=>['required','integer'],
            'note'=>['string'],

        ]);

        if ($validator->fails())
        {
            return validationError($validator->errors());
        }
        $model=SubTask::find($request->id);
        $model->name=$request->name;
        $model->task_id=$request->task_id;
        $model->note=$request->note;
        $model->status=TaskStatus::TASK_WAITING;

        $model->save();

        return updateSuccess($model);
    }
    public function tree(Request $request): JsonResponse
    {
        paginate($request, $limit, $offset);
        $subTaskQuery = SubTask::query();

//        if($request->has('name')) {
//            $subTaskQuery->where('name', 'like', filter($request->get('name')));
//        }

        $count = $subTaskQuery->count();
        $sub_tasks = $subTaskQuery->limit($request->get('limit'))->offset($request->get('offset'))->get();

        $data = simpleTree($sub_tasks);
        return response()->json(['data' => $data, 'total' => $count]);
    }
    public function single($id){
        $model= SubTask::query()->find($id);

        if (!$model) {
            return notFoundError($id);
        }

        return response()->json($model);
    }
    public function delete($id){
        if(checkRole()==Roles::WORKER ){
            return permissionError();
        }
        $model= SubTask::query()->find($id);

        if (!$model) {
            return notFoundError($id);
        }
        $delete=checkIfExist('CheckList','sub_task_id',$id);
        if($delete==1){
            return notDeleteError();
        }
        SubTask::where('id', '=', $id)->delete();

        return deleted();

    }


}
