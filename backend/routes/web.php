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

// API Routes
$router->group(['prefix' => 'api'], function () use ($router) {

    // Authentication routes (public)
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');

    // Protected routes
    $router->group(['middleware' => 'jwt.auth'], function () use ($router) {

        // Auth routes
        $router->post('logout', 'AuthController@logout');
        $router->get('me', 'AuthController@me');
        $router->post('refresh', 'AuthController@refresh');

        // User routes
        $router->get('users', 'UserController@index');
        $router->post('users', 'UserController@store');
        $router->get('users/{id}', 'UserController@show');
        $router->put('users/{id}', 'UserController@update');
        $router->delete('users/{id}', 'UserController@destroy');

        // Task routes
        $router->get('tasks', 'TaskController@index');
        $router->post('tasks', 'TaskController@store');
        $router->get('tasks/{id}', 'TaskController@show');
        $router->put('tasks/{id}', 'TaskController@update');
        $router->patch('tasks/{id}/status', 'TaskController@updateStatus');
        $router->delete('tasks/{id}', 'TaskController@destroy');

        // Get tasks for specific user
        $router->get('users/{userId}/tasks', 'TaskController@getUserTasks');

        // API Log routes
        $router->get('logs', 'ApiLogController@index');
        $router->get('logs/statistics', 'ApiLogController@statistics');
        $router->get('logs/{id}', 'ApiLogController@show');
    });
});
