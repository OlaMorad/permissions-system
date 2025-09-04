<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('backup:run')->dailyAt('20:00');
Schedule::command('transactions:archive-rejected')->hourly();
Schedule::command('exams:update-candidates-count')->daily();
Schedule::command('programs:auto-reject')->daily();
Schedule::command('programs:activate')->daily();
Schedule::command('exams:update-status')->everyMinute();
Schedule::command('exams:update-present-candidates')->everyTenMinutes();
Schedule::command('email-verifications:clear-expired')->everyMinute();
Schedule::command('exam:send-password')->everyMinute();
Schedule::command('mails:notify-unread')->dailyAt('10:00');
Schedule::command('exams:notify-closing')->daily();
