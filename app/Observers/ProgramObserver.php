<?php

namespace App\Observers;

use App\Enums\ExamRequestEnum;
use App\Models\Program;
use App\Services\FirebaseNotificationService;

class ProgramObserver
{
    public function __construct(
        protected FirebaseNotificationService $notification,

    ) {}
    /**
     * Handle the Program "created" event.
     */
    public function created(Program $program): void
    {
        $title = "تم إضافة برنامج امتحاني جديد";
        $body  = "برنامج شهر {$program->month} - سنة {$program->year}";

        $data = [
            'program_id' => $program->id,
            'month'      => $program->month,
            'year'       => $program->year,
            'exams_count' => $program->exams_count,
        ];

        // إرسال إشعار للمدير"
        $this->notification->sendToRole('المدير', $title, $body, $data);
    }

    /**
     * Handle the Program "updated" event.
     */
    public function updated(Program $program): void
    {
        if ($program->wasChanged('approved')) {
            $newStatus = $program->approved;

            if (in_array($newStatus->value, [ExamRequestEnum::APPROVED->value, ExamRequestEnum::REJECTED->value])) {
                $title = "تحديث حالة برنامج امتحاني";
                $body  = "تم تغيير حالة البرنامج ({$program->month}/{$program->year}) إلى: {$newStatus->value}";

                $data = [
                    'program_id' => $program->id,
                    'month'      => $program->month,
                    'year'       => $program->year,
                    'new_status' => $newStatus->value,
                ];

                // إشعار لرئيس الامتحانات
                $this->notification->sendToRole('رئيس الامتحانات', $title, $body, $data);

                if ($newStatus === ExamRequestEnum::APPROVED->value) {
                    $doctorTitle = "تم إصدار برنامج الامتحان";
                    $doctorBody  = "برنامج شهر {$program->month} - سنة {$program->year}";

                    $this->notification->sendToRole('الطبيب',$doctorTitle,$doctorBody, $data);
                }
            }
        }
    }

    /**
     * Handle the Program "deleted" event.
     */
    public function deleted(Program $program): void
    {
        //
    }

    /**
     * Handle the Program "restored" event.
     */
    public function restored(Program $program): void
    {
        //
    }

    /**
     * Handle the Program "force deleted" event.
     */
    public function forceDeleted(Program $program): void
    {
        //
    }
}
