<?php

namespace App\Providers;

use App\Models\Manager;
use App\Models\Employee;
use App\Models\ExamRequest;
use App\Models\FormContent;
use App\Models\Transaction;
use App\Models\Announcement;
use App\Models\Form;
use App\Models\Program;
use App\Models\Specialization;
use App\Observers\ManagerObserver;
use App\Observers\employeeObserver;
use App\Observers\ExamRequestObserver;
use App\Observers\FormContentObserver;
use App\Observers\TransactionObserver;
use App\Observers\AnnouncementObserver;
use App\Observers\FormObserver;
use App\Observers\ProgramObserver;
use App\Observers\SpecializationObserver;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
            // $this->app->bind(ClientInterface::class, Client::class);
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
        ExamRequest::observe(ExamRequestObserver::class);
        Announcement::observe(AnnouncementObserver::class);
        Form::observe(FormObserver::class);
        Program::observe(ProgramObserver::class);
        Specialization::observe(SpecializationObserver::class);
    }
}
