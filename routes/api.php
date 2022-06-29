<?php

use App\Http\Controllers\Apps\Auth\AuthController;
use App\Http\Controllers\Apps\MobileApi\HomeMain\HomeMainController;
use App\Http\Controllers\Apps\MobileApi\Profile\ProfileController;
use App\Http\Controllers\Apps\MobileApi\UserController;
use App\Http\Controllers\CompanyJobStatus\CompanyJobStatusController;
use App\Http\Controllers\Web\Attendance\AttendanceController;
use App\Http\Controllers\Web\Auth\AuthController as WebAuthController;
use App\Http\Controllers\Web\CompanyBranch\CompanyBranchController;
use App\Http\Controllers\Web\CompanyDivision\CompanyDivisionController;
use App\Http\Controllers\Web\Dashboard\DashboardController;
use App\Http\Controllers\Web\Dataset\DatasetController;
use App\Http\Controllers\Web\Profile\ProfileController as ProfileProfileController;
use App\Http\Controllers\Web\RolePermission\RolePermissionController;
use App\Http\Controllers\Web\UserManagement\UserManagementController;
use App\Models\CompanyJobStatus;
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
        Route::group(['middleware' => 'auth:sanctum:employee'], function () {
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

            Route::group(['middleware' => ['auth:sanctum:manager']], function () {
                Route::post('validate', [WebAuthController::class, 'validateToken']);
                Route::post('logout', [WebAuthController::class, 'logout']);

                Route::group(['middleware' => ['branch_check']], function() {

                    /* DASHBOARD */
                    Route::group(['prefix' => 'dashboard'], function () {
                        Route::get('activities', [DashboardController::class , 'logActivities']);
                        Route::get('chart-employee', [DashboardController::class, 'getChartEmployee']);
                        Route::get('chart-workplaces', [DashboardController::class, 'getChartWorkplacesEmployee']);
                    });
                    
                    /* Role */
                    Route::group(['prefix' => 'role'], function() {
                        Route::get('', [RolePermissionController::class, 'listRole']);
                        Route::get('permissions', [RolePermissionController::class, 'listPermissions']);
                        Route::get('{id}', [RolePermissionController::class, 'detailRoleWithPermissions']);
                        Route::post('', [RolePermissionController::class, 'createRolePermissions']);
                        Route::put('{id}', [RolePermissionController::class, 'updateRolePermissions']);
                        Route::delete('{id}', [RolePermissionController::class, 'deleteRole']);
                    });
    
                    /* User Manager */
                    Route::group(['prefix' => 'user'], function() {
                        Route::get('', [UserManagementController::class, 'index']);
                        Route::get('{id}', [UserManagementController::class, 'show']);
                        Route::post('', [UserManagementController::class, 'create']);
                        Route::put('{id}', [UserManagementController::class, 'update']);
                        Route::delete('{id}', [UserManagementController::class, 'destroy']);
                        Route::post('resend/{id}', [UserManagementController::class, 'resendInvitation']);
                    });
    
                    /* Profile */
                    Route::group(['prefix' => 'profile'], function() {
                        Route::put('password', [ProfileProfileController::class, 'updatePassword']);
                        Route::post('picture', [ProfileProfileController::class, 'updateImage']);
                    });
    
                    /* Dataset */
                    Route::group(['prefix' => 'dataset'], function () {
                        Route::get('employee', [DatasetController::class, 'employee']);
                        Route::get('employee/{id}', [DatasetController::class, 'detailEmployee']);
                        Route::get('role-manager', [DatasetController::class, 'roleManager']);
                        Route::get('department', [DatasetController::class, 'listDepartment']);
                        Route::get('job-status', [DatasetController::class, 'listJobStatus']);
                        Route::get('company-branch', [DatasetController::class, 'listCompanyBranch']);
                    });
    
                    /* Attendance */
                    Route::group(['prefix' => 'attendance'], function() {
                        Route::get('', [AttendanceController::class, 'index']);
                        Route::get('{id}', [AttendanceController::class, 'show']);
                    });
    
                    /* Division or Department */
                    Route::resource('division', CompanyDivisionController::class);
    
                    /* Job Status */
                    Route::resource('job-status', CompanyJobStatusController::class);
    
                    /* Company Branch */
                    Route::resource('company-branch', CompanyBranchController::class);
                    Route::get('company-branch/branch/{branch_code}', [CompanyBranchController::class, 'validateBranchCode']);
                });
            });


        });
    });
});
