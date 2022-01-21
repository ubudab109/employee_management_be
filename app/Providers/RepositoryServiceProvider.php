<?php

namespace App\Providers;

use App\Repositories\CompanySetting\CompanySettingInterface;
use App\Repositories\CompanySetting\CompanySettingRepository;
use App\Repositories\EmployeeNote\EmployeeNoteInterface;
use App\Repositories\EmployeeNote\EmployeeNoteRepository;
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
        /** REPOSITORY AND INTERFACE */
        $this->app->bind(CompanySettingInterface::class, CompanySettingRepository::class);
        $this->app->bind(EmployeeNoteInterface::class, EmployeeNoteRepository::class);
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
