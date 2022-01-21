<?php

use App\Http\Controllers\Apps\Auth\AuthController;
use App\Http\Controllers\Apps\MobileApi\HomeMain\HomeMainController;
use App\Http\Controllers\Apps\MobileApi\Profile\ProfileController;
use App\Http\Controllers\Apps\MobileApi\UserController;
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

Route::group(['prefix' => 'v1'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('user', [UserController::class, 'index']);

        Route::group(['prefix' => 'home'], function () {
            Route::get('working-hours', [HomeMainController::class, 'workingHours']);
            Route::get('event-note', [HomeMainController::class, 'getEventDate']);
            Route::group(['prefix' => 'note'], function () {
                Route::get('', [HomeMainController::class, 'employeeNote']);
                Route::post('', [HomeMainController::class, 'createNote']);
                Route::delete('{id}', [HomeMainController::class, 'deleteEmployeeNote']);
            });
        });

        Route::group(['prefix' => 'profile'], function () {
            Route::post('picture', [ProfileController::class, 'uploadImage']);
        });
        Route::post('logout', [AuthController::class, 'logout']);
    });
});
