<?php

namespace App\Http\Controllers;


use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\Workers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Roles;

class ProjectController extends Controller
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
        $projectQuery = Project::query();

        if($request->has('name')) {
            $projectQuery->where('name', 'like', filter($request->get('name')));
        }

        $count = $projectQuery->count();
        $projects = $projectQuery->limit($request->get('limit'))->offset($request->get('offset'))->get();


        return response()->json(['data' => $projects, 'total' => $count]);
    }
    public function store(Request $request){
        if(checkRole()!=Roles::SYS_OWNER &&checkRole()!=Roles::COMPANY_OWNER){
            return permissionError();
        }
        $validator = Validator::make($request->all(), [
            'name'=>['required','string','unique:departaments'],
            'client_id'=>['required','integer'],
            'departament_id'=>['required','integer'],
            'project_manager'=>'required'

        ]);
        if ($validator->fails())
        {
            return validationError($validator->errors());
        }
        $model= new Project();
        $model->name=$request->name;
        $model->departament_id=$request->departament_id;
        $model->client_id=$request->client_id;
        $model->status=1;
        $model->save();

        foreach ($request->project_manager as $user_id){
            $project= new ProjectManager();
            $project->project_id=$model->id;
            $project->user_id=$user_id;
            $project->save();
        }

        return createSuccess($model);
    }
    public function update(Request $request)
    {
        if(checkRole()!=Roles::SYS_OWNER &&checkRole()!=Roles::COMPANY_OWNER){
            return permissionError();
        }
        $validator = Validator::make($request->all(), [
            'name'=>['string',Rule::unique('departaments')->ignore($request->id)],
            'client_id'=>['required','integer'],
            'departament_id'=>['required','integer'],
            'status'=>['required','integer'],

        ]);

        if ($validator->fails())
        {
            return validationError($validator->errors());
        }
        $model=Project::find($request->id);
        $model->name=$request->name;
        $model->departament_id=$request->departament_id;
        $model->client_id=$request->client_id;
        $model->status=$request->status;

        $model->save();

        ProjectManager::where(['project_id'=>$request->id])->delete();

        foreach ($request->project_manager as $user_id){
            $project= new ProjectManager();
            $project->project_id=$model->id;
            $project->user_id=$user_id;
            $project->save();
        }
        return updateSuccess($model);
    }
    public function tree(Request $request): JsonResponse
    {
        paginate($request, $limit, $offset);
        $projectQuery = Project::query();

        if($request->has('name')) {
            $projectQuery->where('name', 'like', filter($request->get('name')));
        }

        $count = $projectQuery->count();
        $projects = $projectQuery->limit($request->get('limit'))->offset($request->get('offset'))->get();

        $data = simpleTree($projects);
        return response()->json(['data' => $data, 'total' => $count]);
    }
    public function single($id){
        $model= Project::query()->find($id);

        if (!$model) {
            return notFoundError($id);
        }

        return response()->json($model);
    }
    public function delete($id){
        if(checkRole()!=Roles::SYS_OWNER &&checkRole()!=Roles::COMPANY_OWNER){
            return permissionError();
        }

        $model= Project::query()->find($id);

        if (!$model) {
            return notFoundError($id);
        }
        $delete=checkIfExist('Task','project_id',$id);
        if($delete==1){
            return notDeleteError();
        }
        Project::where('id', '=', $id)->delete();

        return deleted();

    }
    public function addWorker(Request $request): JsonResponse
    {
        if(checkRole()==Roles::PROJECT_MANAGER &&checkRole()==Roles::WORKER){
            return permissionError();
        }
        $validator = Validator::make($request->all(), [
            'project_id'=>['required','integer'],
            'workers'=>['required'],

        ]);

        if ($validator->fails())
        {
            return validationError($validator->errors());
        }
        foreach ($request->workers as $user_id){
            $worker= new Workers();
            $worker->project_id=$request->project_id;
            $worker->user_id=$user_id;
            $worker->save();
        }

       $workers=Workers::query()
           ->select(DB::raw('CONCAT(u.firstName," ", u.lastName) AS Workers'))
           ->leftJoin('users as u','u.id','=','workers.user_id')
//           ->leftJoin('project as p','p.id','=',$request->project_id)
           ->where(['project_id'=>$request->project_id])
           ->get();

        return response()->json(['data' => $workers]);
    }
    public function myProjects(){
        $user=auth()->user();
        if($user->role==Roles::PROJECT_MANAGER){
            $project_list=ProjectManager::select('project_id')->where(['user_id'=>$user->id])->get();
            $lists=array();
            foreach ($project_list as $list){
                array_push($lists,$list->project_id);
            }
            $projects=Project::wherein('id',$lists)->get();
            $count=count($projects);
            $data = simpleTree($projects);
            return response()->json(['data' => $data, 'total' => $count]);
        }
        if($user->role==Roles::TEAM_LEADER){

            $projects=Project::where('departament_id',$user->department_id)->get();
            $count=count($projects);
            $data = simpleTree($projects);
            return response()->json(['data' => $data, 'total' => $count]);
        }
        if($user->role==Roles::WORKER){
            $project_list=Workers::select('project_id')->where(['user_id'=>$user->id])->get();
            $lists=array();
            foreach ($project_list as $list){
                array_push($lists,$list->project_id);
            }
            $projects=Project::wherein('id',$lists)->get();
            $count=count($projects);
            $data = simpleTree($projects);
            return response()->json(['data' => $data, 'total' => $count]);
        }
        $projects=Project::all();
        $count=count($projects);
        $data = simpleTree($projects);
        return response()->json(['data' => $data, 'total' => $count]);
    }

    public function projectDetails($id){
        $query=Task::query()
            ->SELECT ([
                'tasks.id as task_id',
                DB::raw('CONCAT(tl.firstName," ", tl.lastName) AS "team_leader"'),
                DB::raw('CONCAT(w.firstName, " ", w.lastName) as "worker"'),
                DB::raw('tasks.task AS "task"'),
                DB::raw('t.name AS "task_type"'),
                DB::raw('c.name AS "client_name"'),
                DB::raw('p.name AS "project_name"'),
                'tasks.given_date',
                'tasks.start_date',
                'tasks.end_date',
                'tasks.scheduled_day',
                'tasks.note',
            ])
            ->LeftJoin('projects as p','tasks.project_id','=','p.id')
            ->LeftJoin('departaments as d','p.departament_id','=','d.id')
            ->LeftJoin('users as tl', function ($join){
                $join->on('d.id','=','tl.department_id')
                    ->where('tl.role','=',2);
            })
            ->LeftJoin('users as w','tasks.worker_id','=','w.id')
            ->LeftJoin('task_types as t','tasks.task_type','=','t.id')
            ->LeftJoin('clients as c','p.client_id','=','c.id')
            ->get();

        foreach ($query as $data) {
            $data->subtask =SubTask::where(['task_id' => $data->task_id])->get();

        }

        $count=count($query);
        return response()->json(['data' => $query, 'total' => $count]);

    }


}
