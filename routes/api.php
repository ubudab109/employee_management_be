<?php

use App\Http\Controllers\Apps\Auth\AuthController;
use App\Http\Controllers\Apps\MobileApi\HomeMain\HomeMainController;
use App\Http\Controllers\Apps\MobileApi\Profile\ProfileController;
use App\Http\Controllers\Apps\MobileApi\UserController;
use App\Http\Controllers\Web\Auth\AuthController as WebAuthController;
use App\Http\Controllers\Web\Dashboard\DashboardController;
use App\Http\Controllers\Web\Dataset\DatasetController;
use App\Http\Controllers\Web\Profile\ProfileController as ProfileProfileController;
use App\Http\Controllers\Web\RolePermission\RolePermissionController;
use App\Http\Controllers\Web\UserManagement\UserManagementController;
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


Route::group(['middleware' => 'cors'], function() {
    Route::group(['prefix' => 'v1'], function () {
        /** FOR MOBILE */
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
    
        /** FOR APPS */
        Route::group(['prefix' => 'web'], function() {
            Route::post('login', [WebAuthController::class, 'login']);

            Route::group(['middleware' => 'auth:sanctum'], function () {
                Route::post('validate', [WebAuthController::class, 'validateToken']);
                Route::post('logout', [WebAuthController::class, 'logout']);

                Route::group(['prefix' => 'dashboard'], function () {
                    Route::get('activities', [DashboardController::class , 'logActivities']);
                    Route::get('chart-employee', [DashboardController::class, 'getChartEmployee']);
                });
                
                Route::group(['prefix' => 'role'], function() {
                    Route::get('', [RolePermissionController::class, 'listRole']);
                    Route::get('permissions', [RolePermissionController::class, 'listPermissions']);
                    Route::get('{id}', [RolePermissionController::class, 'detailRoleWithPermissions']);
                    Route::post('', [RolePermissionController::class, 'createRolePermissions']);
                    Route::put('{id}', [RolePermissionController::class, 'updateRolePermissions']);
                    Route::delete('{id}', [RolePermissionController::class, 'deleteRole']);
                });

                Route::group(['prefix' => 'user'], function() {
                    Route::get('', [UserManagementController::class, 'index']);
                    Route::get('{id}', [UserManagementController::class, 'detail']);
                    Route::post('', [UserManagementController::class, 'create']);
                    Route::put('{id}', [UserManagementController::class, 'update']);
                    Route::delete('{id}', [UserManagementController::class, 'delete']);
                    Route::post('resend/{id}', [UserManagementController::class, 'resendInvitation']);
                });

                Route::group(['prefix' => 'profile'], function() {
                    Route::put('password', [ProfileProfileController::class, 'updatePassword']);
                    Route::post('picture', [ProfileProfileController::class, 'updateImage']);
                });

                Route::group(['prefix' => 'dataset'], function () {
                    Route::get('employee', [DatasetController::class, 'employee']);
                    Route::get('employee/{id}', [DatasetController::class, 'detailEmployee']);
                    Route::get('role-manager', [DatasetController::class, 'roleManager']);
                });
            });


        });
    });
});
