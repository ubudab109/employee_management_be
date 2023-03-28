<?php

use App\Http\Controllers\Apps\Auth\AuthController;
use App\Http\Controllers\Apps\MobileApi\HomeMain\HomeMainController;
use App\Http\Controllers\Apps\MobileApi\Profile\ProfileController;
use App\Http\Controllers\Apps\MobileApi\UserController;
use App\Http\Controllers\Web\Payslip\PayslipController;
use App\Http\Controllers\Web\Attendance\AttendanceController;
use App\Http\Controllers\Web\Auth\AuthController as WebAuthController;
use App\Http\Controllers\Web\ClaimType\ClaimTypeController;
use App\Http\Controllers\Web\CompanyBranch\CompanyBranchController;
use App\Http\Controllers\Web\CompanyDivision\CompanyDivisionController;
use App\Http\Controllers\Web\CompanySchedule\CompanyScheduleController;
use App\Http\Controllers\Web\Dashboard\DashboardController;
use App\Http\Controllers\Web\Dataset\DatasetController;
use App\Http\Controllers\Web\Employee\EmployeeController;
use App\Http\Controllers\Web\EmployeeOvertime\EmployeeOvertimeController;
use App\Http\Controllers\Web\EmployeePaidLeave\EmployeePaidLeaveController;
use App\Http\Controllers\Web\EmployeeReimbersement\EmployeeReimbersementController;
use App\Http\Controllers\Web\Profile\ProfileController as ProfileProfileController;
use App\Http\Controllers\Web\RolePermission\RolePermissionController;
use App\Http\Controllers\Web\SalaryComponent\SalaryComponentController;
use App\Http\Controllers\Web\UserManagement\UserManagementController;
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
                        Route::get('employee-notin-payslip', [DatasetController::class, 'employeeNotInPayslip']);
                        Route::get('exists-employee', [DatasetController::class, 'checkEmployee']);
                        Route::get('employee/{id}', [DatasetController::class, 'detailEmployee']);
                        Route::get('role-manager', [DatasetController::class, 'roleManager']);
                        Route::get('department', [DatasetController::class, 'listDepartment']);
                        Route::get('job-status', [DatasetController::class, 'listJobStatus']);
                        Route::get('company-branch', [DatasetController::class, 'listCompanyBranch']);
                        Route::get('salary-component', [DatasetController::class, 'salaryComponent']);
                        Route::get('total-working', [DatasetController::class, 'getWorkinDays']);
                        Route::get('claim-type', [DatasetController::class, 'listClaimType']);
                        Route::get('holidays', [DatasetController::class, 'getHolidays']);
                    });
    
                    /* Attendance */
                    Route::group(['prefix' => 'attendance'], function() {
                        Route::get('', [AttendanceController::class, 'index']);
                        Route::get('{id}', [AttendanceController::class, 'show']);
                        Route::put('{id}', [AttendanceController::class, 'update']);
                    });
    
                    /* Division or Department */
                    Route::resource('division', CompanyDivisionController::class);
    
                    /* Company Branch */
                    Route::resource('company-branch', CompanyBranchController::class);
                    Route::get('company-branch/branch/{branch_code}', [CompanyBranchController::class, 'validateBranchCode']);

                    /** Employee */
                    // Route::post('employee', [EmployeeController::class, 'store']);
                    Route::resource('employee', EmployeeController::class);
                    Route::put('employee-finance/{id}', [EmployeeController::class, 'updateFinance']);
                    // Route::get('employee/{id}',[EmployeeController::class, 'show']);
                    Route::delete('delete-employee', [EmployeeController::class, 'destroy']);
                    
                    /** EMPLOYEE OVERTIME */
                    Route::resource('employee-overtime', EmployeeOvertimeController::class);
                    Route::put('employee-overtime-status', [EmployeeOvertimeController::class, 'updateStatus']);

                    /** EMPLOYEE PAID LEAVE */
                    Route::resource('employee-paid-leave', EmployeePaidLeaveController::class);

                    /** EMPLOYEE REIUMBERSEMENT */
                    Route::resource('employee-reimbursement', EmployeeReimbersementController::class);

                    /** PAYSLIP */
                    Route::resource('payslip', PayslipController::class);
                    Route::get('payslip-generate-list', [PayslipController::class, 'listPayslipGenerateProcess']);
                    Route::post('payslip-generate', [PayslipController::class, 'generate']);
                    Route::post('payslip-generate/{id}', [PayslipController::class, 'retryGenerate']);
                    Route::post('send-payslip', [PayslipController::class, 'sendPayslip']);
                    
                    /** CLAIM TYPE */
                    Route::resource('claim-type', ClaimTypeController::class);

                    /** COMPANY SCHEDULE */
                    Route::resource('company-schedule', CompanyScheduleController::class);
                    Route::put('change-default-schedule', [CompanyScheduleController::class, 'updateDefaultSchedule']);
                });

                /** SUPERADMIN ROUTE */
                Route::group(['middleware' => ['superadmin_check']], function () {
                    /** SALARY COMPONENT */
                    Route::resource('salary-component', SalaryComponentController::class);
                });
            });


        });
    });
});
