<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Exam;
use App\Models\User;
use Carbon\Carbon;
use App\Services\FirebaseNotificationService;
class NotifyDoctorsExamClosing extends Command
{
    protected $signature = 'exams:notify-closing';
    protected $description = 'إرسال إشعار لجميع الأطباء قبل 3 أيام من إغلاق الطلبات الامتحانية';

    public function handle()
    {
        $targetDate = Carbon::today()->addDays(10);

        $exams = Exam::whereDate('date', $targetDate)->get();

        if ($exams->isEmpty()) {
            $this->info('لا يوجد امتحانات ستغلق بعد 3 أيام.');
            return;
        }

        foreach ($exams as $exam) {
            $doctors = User::whereHas('roles', function($q) {
                $q->where('name', 'الطبيب');
            })->pluck('id');

            foreach ($doctors as $doctor) {
                app(FirebaseNotificationService::class)->sendToUser(
                   (int)$doctor,
                    'تنبيه إغلاق الطلبات الامتحانية',
                    "الطلبات الامتحانية للامتحان بتاريخ {$exam->date->format('Y-m-d')} ستغلق بعد 3 أيام."
                );
            }
        }

        $this->info('تم إرسال الإشعارات بنجاح.');
    }
}
