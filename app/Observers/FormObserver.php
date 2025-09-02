<?php

namespace App\Observers;

use App\Enums\FormStatus;
use App\Models\Form;
use App\Services\FirebaseNotificationService;

class FormObserver
{
    public function __construct(
        protected FirebaseNotificationService $notification,

    ) {}

    /**
     * Handle the Form "created" event.
     */
    public function created(Form $form): void
    {
        $title = "تم إضافة نموذج معاملة جديد";
        $body  = "{$form->name}";

        $data = [
            'form_id' => $form->id,
            'form_name'  => $form->name,
        ];
        $this->notification->sendToRole('المدير', $title, $body, $data);
    }

    /**
     * Handle the Form "updated" event.
     */
    public function updated(Form $form): void
    {
        // تحقق إذا تغيرت الحالة
        if ($form->wasChanged('status')) {
            $oldStatus = $form->getOriginal('status');  // الحالة قبل التغيير
            $newStatus = $form->status;                 // الحالة الجديدة

            // فقط لو انتقل من "قيد الدراسة" إلى "فعالة" أو "مرفوضة"
            if (
                $oldStatus === FormStatus::UNDER_REVIEW->value &&
                in_array($newStatus->value, [FormStatus::Active->value, FormStatus::REJECTED->value])
            ) {

                $title = "تحديث حالة نموذج معاملة";
                $body  = "تم تغيير حالة النموذج '{$form->name}' إلى {$newStatus->value}";

                $data = [
                    'form_id'   => $form->id,
                    'form_name' => $form->name,
                    'new_status' => $newStatus->value,
                ];

                $this->notification->sendToRole('رئيس الديوان', $title, $body, $data);
            }
        }
    }

    /**
     * Handle the Form "deleted" event.
     */
    public function deleted(Form $form): void
    {
        //
    }

    /**
     * Handle the Form "restored" event.
     */
    public function restored(Form $form): void
    {
        //
    }

    /**
     * Handle the Form "force deleted" event.
     */
    public function forceDeleted(Form $form): void
    {
        //
    }
}
