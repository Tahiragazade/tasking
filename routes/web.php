<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});
Route::group([

    'prefix' => 'api'

], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    Route::put('update/{id}', 'AuthController@update');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::get('session', 'AuthController@me');
    Route::get('single/{id}', 'AuthController@singleUser');
    Route::get('all', 'AuthController@allUsers');

    Route::post('departament/store', 'DepartamentController@store');
    Route::put('departament/update/{id}', 'DepartamentController@update');
    Route::get('departament/single/{id}', 'DepartamentController@single');
    Route::get('departament/delete/{id}', 'DepartamentController@delete');
    Route::get('departament/all', 'DepartamentController@all');
    Route::get('departament/tree', 'DepartamentController@tree');

    Route::post('clients/store', 'ClientController@store');
    Route::put('clients/update/{id}', 'ClientController@update');
    Route::get('clients/single/{id}', 'ClientController@single');
    Route::get('clients/delete/{id}', 'ClientController@delete');
    Route::get('clients/all', 'ClientController@all');
    Route::get('clients/tree', 'ClientController@tree');

    Route::post('types/store', 'TaskTypeController@store');
    Route::put('types/update/{id}', 'TaskTypeController@update');
    Route::get('types/single/{id}', 'TaskTypeController@single');
    Route::get('types/delete/{id}', 'TaskTypeController@delete');
    Route::get('types/all', 'TaskTypeController@all');
    Route::get('types/tree', 'TaskTypeController@tree');

    Route::post('projects/store', 'ProjectController@store');
    Route::put('projects/update/{id}', 'ProjectController@update');
    Route::get('projects/single/{id}', 'ProjectController@single');
    Route::get('projects/delete/{id}', 'ProjectController@delete');
    Route::get('projects/all', 'ProjectController@all');
    Route::get('projects/tree', 'ProjectController@tree');
    Route::post('projects/add-worker', 'ProjectController@addWorker');

    Route::post('tasks/store', 'TaskController@store');
    Route::put('tasks/update/{id}', 'TaskController@update');
    Route::get('tasks/single/{id}', 'TaskController@single');
    Route::get('tasks/delete/{id}', 'TaskController@delete');
    Route::get('tasks/all', 'TaskController@all');
    Route::get('tasks/tree', 'TaskController@tree');
    Route::get('tasks/statuses', 'TaskController@statusTree');

});
