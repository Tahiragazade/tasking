<?php

namespace App\Http\Controllers;


use App\Models\Project;
use App\Models\ProjectManager;
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
    public function delete(){
        if(checkRole()!=Roles::SYS_OWNER &&checkRole()!=Roles::COMPANY_OWNER){
            return permissionError();
        }

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
}
