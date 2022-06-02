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

class TaskController extends Controller
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
        $taskQuery = Task::query();

//        if($request->has('name')) {
//            $taskQuery->where('name', 'like', filter($request->get('name')));
//        }

        $count = $taskQuery->count();
        $tasks = $taskQuery->limit($request->get('limit'))->offset($request->get('offset'))->get();


        return response()->json(['data' => $tasks, 'total' => $count]);
    }
    public function store(Request $request){
        if(checkRole()==Roles::WORKER){
            return permissionError();
        }
        $validator = Validator::make($request->all(), [
            'worker_id'=>['required','integer'],
            'task'=>['required','string'],
            'task_type'=>['required','integer'],
            'project_id'=>['required','integer'],
            'given_date'=>['required','date'],
            'start_date'=>['date'],
            'end_date'=>['date'],
            'scheduled_day'=>['required','integer'],
            'note'=>['string'],

        ]);
        if ($validator->fails())
        {
            return validationError($validator->errors());
        }
        $model= new Task();
        $model->worker_id=$request->worker_id;
        $model->task=$request->task;
        $model->task_type=$request->task_type;
        $model->note=$request->note;
        $model->project_id=$request->project_id;
        $model->given_date=$request->given_date;
        $model->end_date=$request->end_date;
        $model->scheduled_day=$request->scheduled_day;
        $model->task_status=TaskStatus::TASK_WAITING;
        $model->created_by=Auth::id();

        $model->save();


        return createSuccess($model);
    }
    public function update(Request $request)
    {
        if(checkRole()==Roles::WORKER){
            return permissionError();
        }
        $validator = Validator::make($request->all(), [
            'worker_id'=>['required','integer'],
            'task'=>['required','string'],
            'task_type'=>['required','integer'],
            'project_id'=>['required','integer'],
            'given_date'=>['required','date'],
            'start_date'=>['date'],
            'end_date'=>['date'],
            'scheduled_day'=>['required','integer'],
            'note'=>['string'],

        ]);

        if ($validator->fails())
        {
            return validationError($validator->errors());
        }
        $model=Task::find($request->id);
        $model->worker_id=$request->worker_id;
        $model->task=$request->task;
        $model->task_type=$request->task_type;
        $model->note=$request->note;
        $model->project_id=$request->project_id;
        $model->given_date=$request->given_date;
        $model->end_date=$request->end_date;
        $model->scheduled_day=$request->scheduled_day;
        $model->task_status=TaskStatus::TASK_WAITING;
        $model->created_by=Auth::id();

        $model->save();

        return updateSuccess($model);
    }
    public function tree(Request $request): JsonResponse
    {
        paginate($request, $limit, $offset);
        $taskQuery = Task::query();

//        if($request->has('name')) {
//            $taskQuery->where('name', 'like', filter($request->get('name')));
//        }

        $count = $taskQuery->count();
        $tasks = $taskQuery->limit($request->get('limit'))->offset($request->get('offset'))->get();

        $data = taskTree($tasks);
        return response()->json(['data' => $data, 'total' => $count]);
    }
    public function single($id){
        $model= Task::query()->find($id);

        if (!$model) {
            return notFoundError($id);
        }

        return response()->json($model);
    }
    public function delete($id){
        if(checkRole()==Roles::WORKER ){
            return permissionError();
        }
        $model= Task::query()->find($id);

        if (!$model) {
            return notFoundError($id);
        }
        $delete=checkIfExist('SubTask','task_id',$id);
        $delete_2=checkIfExist('CheckList','sub_task_id',$id);
        if($delete==1 || $delete_2==1){
            return notDeleteError();
        }
        Task::where('id', '=', $id)->delete();

        return deleted();

    }
    public function statusTree(){
        $data = statusTree();
        $count=count($data);
        return response()->json(['data' => $data, 'total' => $count]);
    }

}
