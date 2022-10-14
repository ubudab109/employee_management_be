<?php

namespace App\Providers;

use App\Repositories\CompanyBranch\CompanyBranchInterface;
use App\Repositories\CompanyBranch\CompanyBranchRepository;
use App\Repositories\CompanyDivision\CompanyDivisionInterface;
use App\Repositories\CompanyDivision\CompanyDivisionRepository;
use App\Repositories\CompanySetting\CompanySettingInterface;
use App\Repositories\CompanySetting\CompanySettingRepository;
use App\Repositories\EmployeeAttendance\EmployeeAttendanceInterface;
use App\Repositories\EmployeeAttendance\EmployeeAttendanceRepository;
use App\Repositories\EmployeeNote\EmployeeNoteInterface;
use App\Repositories\EmployeeNote\EmployeeNoteRepository;
use App\Repositories\RolePermissionManager\RolePermissionManagerInterface;
use App\Repositories\RolePermissionManager\RolePermissionManagerRepository;
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
