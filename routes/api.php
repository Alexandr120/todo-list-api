<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::controller(\App\Http\Controllers\Api\AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});

Route::resource('tasks', \App\Http\Controllers\Api\TaskController::class);

Route::group(['prefix' => 'tasks'], function (){
    Route::post('/ajaxFilter', [\App\Http\Controllers\Api\TaskController::class, 'ajaxFilter']);
    Route::post('/complete/{task}', [\App\Http\Controllers\Api\TaskController::class, 'complete']);
});



