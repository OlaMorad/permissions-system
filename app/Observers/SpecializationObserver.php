<?php

namespace App\Observers;

use App\Models\Specialization;
use App\Services\FirebaseNotificationService;

class SpecializationObserver
{
    public function __construct(
        protected FirebaseNotificationService $notification,
    ) {}

    /**
     * Handle the Specialization "created" event.
     */
    public function created(Specialization $specialization): void
    {
        $title = "تم إضافة اختصاص جديد";
        $body  = "يرجى النظر في حال إدراجه ضمن قوائم الاختصاصات في الهيئة";

        $data = [
            'specialization_id' => $specialization->id,
            'specialization_name' => $specialization->name,
            'status' => $specialization->status,
            'bachelors_degree' => $specialization->bachelors_degree,
           // 'experience_years' => $specialization->experience_years,
            'created_at' => $specialization->created_at,

        ];

        $this->notification->sendToRole('المدير', $title, $body, $data);
    }

    /**
     * Handle the Specialization "updated" event.
     */
    public function updated(Specialization $specialization): void
    {
        $changes = $specialization->getDirty();

        // لو التغيير الوحيد هو status → إشعار لرئيس الامتحانات
        if (count($changes) === 1 && array_key_exists('status', $changes)) {
            $title = "تمت الموافقة على الاختصاص";
            $body  = "تمت الموافقة على الاختصاص '{$specialization->nam}' الذي قمت بإدراجه";

            $data = [
                'specialization_id' => $specialization->id,
                'specialization_name' => $specialization->name,
                'bachelors_degree' => $specialization->bachelors_degree,
                'experience_years' => $specialization->experience_years,
                'status' => $specialization->status,
            ];

            $this->notification->sendToRole('رئيس الامتحانات', $title, $body, $data);
            return;
        }

        // أي تغييرات أخرى (غير status) → إشعار للمدير
        if (!empty($changes) && !array_key_exists('status', $changes)) {
            $title = "تم تعديل اختصاص";
            $body  = "يرجى مراجعة التعديلات التي تمت على بيانات الاختصاص";

            $data = [
                'specialization_id' => $specialization->id,
                'specialization_name' => $specialization->name,
                'status' => $specialization->status,
                'bachelors_degree' => $specialization->bachelors_degree,
                'experience_years' => $specialization->experience_years,
                'updated_at' => $specialization->updated_at,
                'changed_fields' => $changes,
            ];

            $this->notification->sendToRole('المدير', $title, $body, $data);
        }
    }

    /**
     * Handle the Specialization "deleted" event.
     */
    public function deleted(Specialization $specialization): void
    {
        //
    }

    /**
     * Handle the Specialization "restored" event.
     */
    public function restored(Specialization $specialization): void
    {
        //
    }

    /**
     * Handle the Specialization "force deleted" event.
     */
    public function forceDeleted(Specialization $specialization): void
    {
        //
    }
}
