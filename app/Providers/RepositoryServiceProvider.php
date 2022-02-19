<?php

namespace App\Providers;

use App\Repositories\CompanySetting\CompanySettingInterface;
use App\Repositories\CompanySetting\CompanySettingRepository;
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
