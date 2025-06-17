<?php

namespace App\Providers;

use App\Models\Manager;
use App\Models\Employee;
use App\Models\FormContent;
use App\Models\Transaction;
use App\Observers\ManagerObserver;
use App\Observers\employeeObserver;
use App\Observers\FormContentObserver;
use App\Observers\TransactionObserver;
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
        FormContent::observe(FormContentObserver::class);
        Transaction::observe(TransactionObserver::class);
    }
}
