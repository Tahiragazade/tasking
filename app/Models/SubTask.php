<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

//this is new
use Tymon\JWTAuth\Contracts\JWTSubject;

class Task extends Model
{

    protected $table = 'tasks';
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    protected $fillable = [
        'worker_id',
        'task',
        'task_type',
        'project_id',
        'given_date',
        'start_date',
        'end_date',
        'scheduled_day',
        'note',
        'created_by',

    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

}
