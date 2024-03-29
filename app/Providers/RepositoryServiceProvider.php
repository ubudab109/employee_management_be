<?php

namespace App\Providers;

use App\Repositories\ClaimType\ClaimTypeInterface;
use App\Repositories\ClaimType\ClaimTypeRepository;
use App\Repositories\CompanyBranch\CompanyBranchInterface;
use App\Repositories\CompanyBranch\CompanyBranchRepository;
use App\Repositories\CompanyDivision\CompanyDivisionInterface;
use App\Repositories\CompanyDivision\CompanyDivisionRepository;
use App\Repositories\CompanySchedule\CompanyScheduleInterface;
use App\Repositories\CompanySchedule\CompanyScheduleRepository;
use App\Repositories\CompanySetting\CompanySettingInterface;
use App\Repositories\CompanySetting\CompanySettingRepository;
use App\Repositories\Employee\EmployeeInterface;
use App\Repositories\Employee\EmployeeRepository;
use App\Repositories\EmployeeAttendance\EmployeeAttendanceInterface;
use App\Repositories\EmployeeAttendance\EmployeeAttendanceRepository;
use App\Repositories\EmployeeLeave\EmployeeLeaveInterface;
use App\Repositories\EmployeeLeave\EmployeeLeaveRepository;
use App\Repositories\EmployeeNote\EmployeeNoteInterface;
use App\Repositories\EmployeeNote\EmployeeNoteRepository;
use App\Repositories\EmployeeOvertime\EmployeeOvertimeInterface;
use App\Repositories\EmployeeOvertime\EmployeeOvertimeRepository;
use App\Repositories\EmployeeReimbersement\EmployeeReimbursementInterface;
use App\Repositories\EmployeeReimbersement\EmployeeReimbursementRepository;
use App\Repositories\ExcelTask\ExcelTaskInterface;
use App\Repositories\ExcelTask\ExcelTaskRepository;
use App\Repositories\Payroll\PayrollInterface;
use App\Repositories\Payroll\PayrollRepository;
use App\Repositories\RolePermissionManager\RolePermissionManagerInterface;
use App\Repositories\RolePermissionManager\RolePermissionManagerRepository;
use App\Repositories\SalaryComponent\SalaryComponentInterface;
use App\Repositories\SalaryComponent\SalaryComponentRepository;
use App\Repositories\UserManagement\UserManagementInterface;
use App\Repositories\UserManagement\UserManagementRepository;
use App\Repositories\UserVerification\UserVerificationInterface;
use App\Repositories\UserVerification\UserVerificationRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        /** INTERFACE AND REPOSITORY */
        $this->app->bind(CompanySettingInterface::class, CompanySettingRepository::class);
        $this->app->bind(EmployeeNoteInterface::class, EmployeeNoteRepository::class);
        $this->app->bind(RolePermissionManagerInterface::class, RolePermissionManagerRepository::class);
        $this->app->bind(UserManagementInterface::class, UserManagementRepository::class);
        $this->app->bind(UserVerificationInterface::class, UserVerificationRepository::class);
        $this->app->bind(EmployeeAttendanceInterface::class, EmployeeAttendanceRepository::class);
        $this->app->bind(CompanyDivisionInterface::class, CompanyDivisionRepository::class);
        $this->app->bind(CompanyBranchInterface::class, CompanyBranchRepository::class);
        $this->app->bind(EmployeeInterface::class, EmployeeRepository::class);
        $this->app->bind(EmployeeOvertimeInterface::class, EmployeeOvertimeRepository::class);
        $this->app->bind(EmployeeLeaveInterface::class, EmployeeLeaveRepository::class);
        $this->app->bind(SalaryComponentInterface::class, SalaryComponentRepository::class);
        $this->app->bind(EmployeeReimbursementInterface::class, EmployeeReimbursementRepository::class);
        $this->app->bind(PayrollInterface::class, PayrollRepository::class);
        $this->app->bind(ClaimTypeInterface::class, ClaimTypeRepository::class);
        $this->app->bind(CompanyScheduleInterface::class, CompanyScheduleRepository::class);
        $this->app->bind(ExcelTaskInterface::class, ExcelTaskRepository::class);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
