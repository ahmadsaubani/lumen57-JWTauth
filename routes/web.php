<?php

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
// $router->put('/users/change/{id}', 'AuthController@update');
// $router->get('/login', function (Request $request) {
//     $token = app('auth')->attempt($request->only('email', 'password'));

//     return response()->json(compact('token'));
// });

$router->post('login', 'AuthController@login');
$router->post('/register', 'AuthController@register');

$router->group(['middleware' => 'auth:api'], function($router)
{
    $router->get('/users', 'AuthController@getAllUser');
    $router->get('/users/{id}', 'AuthController@getUserById');
    $router->post('/users/checkLogin', 'AuthController@me');
    $router->put('/users/change', 'AuthController@update');
    $router->post('/users/delete/{id}', 'AuthController@delete');
    $router->post('/users/logout', 'AuthController@logout');
    $router->post('/users/refresh', 'AuthController@refresh');
});