<?php

namespace App\Providers;

use App\Models\Manager;
use App\Models\Employee;
use App\Observers\ManagerObserver;
use App\Observers\employeeObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Manager::observe(ManagerObserver::class);
            Employee::observe(employeeObserver::class);

    }
}
