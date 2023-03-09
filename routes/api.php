<?php

use App\Http\Controllers\ActionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
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

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('broadcasting/auth', [UserController::class,'checkRoomAccess']);
    Route::post('test.socket', [UserController::class,'testSocket']);

    Route::post('logout', [UserController::class,'logout']);
    Route::post('user/create', [UserController::class,'create']);
    Route::post('user/list', [UserController::class,'list']);
    Route::post('user/edit', [UserController::class,'edit']);
    Route::post('user/destroy', [UserController::class,'destroy']);

    Route::post('action/create', [ActionController::class,'create']);
    Route::post('action/list', [ActionController::class,'list']);
    Route::post('action/edit', [ActionController::class,'edit']);
    Route::post('action/destroy', [ActionController::class,'destroy']);
    Route::post('action/share', [ActionController::class,'share']);
    Route::post('action/statuses', [ActionController::class,'statuses']);
});
