<?php

namespace App\Http\Controllers;


use App\Models\CheckList;
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

class CheckListController extends Controller
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
        $checkListQuery = CheckList::query();

//        if($request->has('name')) {
//            $checkListQuery->where('name', 'like', filter($request->get('name')));
//        }

        $count = $checkListQuery->count();
        $check_lists = $checkListQuery->limit($request->get('limit'))->offset($request->get('offset'))->get();


        return response()->json(['data' => $check_lists, 'total' => $count]);
    }
    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'name'=>['required','string'],
            'sub_task_id'=>['required','integer'],
            'type'=>['required','integer'],
            'note'=>['string'],

        ]);
        if ($validator->fails())
        {
            return validationError($validator->errors());
        }
        $model= new CheckList();
        $model->name=$request->name;
        $model->sub_task_id=$request->sub_task_id;
        $model->note=$request->note;
        $model->type=$request->type;
        $model->status=TaskStatus::TASK_WAITING;
        $model->created_by=Auth::id();

        $model->save();


        return createSuccess($model);
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>['required','string'],
            'sub_task_id'=>['required','integer'],
            'type'=>['required','integer'],
            'note'=>['string'],

        ]);

        if ($validator->fails())
        {
            return validationError($validator->errors());
        }
        $model=CheckList::find($request->id);
        $model->name=$request->name;
        $model->sub_task_id=$request->sub_task_id;
        $model->note=$request->note;
        $model->type=$request->type;
        $model->status=TaskStatus::TASK_WAITING;

        $model->save();

        return updateSuccess($model);
    }
    public function tree(Request $request): JsonResponse
    {
        paginate($request, $limit, $offset);
        $checkListQuery = CheckList::query();

//        if($request->has('name')) {
//            $checkListQuery->where('name', 'like', filter($request->get('name')));
//        }

        $count = $checkListQuery->count();
        $check_lists = $checkListQuery->limit($request->get('limit'))->offset($request->get('offset'))->get();

        $data = simpleTree($check_lists);
        return response()->json(['data' => $data, 'total' => $count]);
    }
    public function single($id){
        $model= CheckList::query()->find($id);

        if (!$model) {
            return notFoundError($id);
        }

        return response()->json($model);
    }
    public function delete(){
        if(checkRole()==Roles::WORKER ){
            return permissionError();
        }

    }


}
