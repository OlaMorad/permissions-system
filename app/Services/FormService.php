<?php

namespace App\Services;

use App\Models\Form;
use App\Enums\FormStatus;
use App\Http\Resources\successResource;
use App\Http\Resources\failResource;

class FormService
{

     // تغيير حالة النموذج من قيد الدراسة إلى فعالة.

    public function changeUnderReviewToActive(int $formId)
    {
        return $this->changeStatus(
            $formId,
            FormStatus::UNDER_REVIEW,
            FormStatus::Active
        );
    }


     // تبديل حالة النموذج بين فعالة وغير فعالة فقط.

    public function toggleActiveStatus(int $formId)
    {
        $form = Form::find($formId);

        if (!$form) {
            return new failResource(['النموذج غير موجود.']);
        }

        if ($form->status === FormStatus::Active) {
            $form->status = FormStatus::Inactive->value;
        } elseif ($form->status === FormStatus::Inactive) {
            $form->status = FormStatus::Active->value;
        } else {
            return new failResource([
                'لا يمكن التبديل إلا بين الحالات: فعالة أو غير فعالة.'
            ]);
        }

        if (!$form->save()) {
            return new failResource(['فشل في حفظ التغييرات.']);
        }

        return new successResource(['new_status' => $form->status]);
    }


     // دالة داخلية عامة لتغيير الحالة إذا تطابقت الحالة الحالية مع المتوقعة.

    private function changeStatus(int $formId, FormStatus $expectedCurrentStatus, FormStatus $newStatus)
    {
        $form = Form::find($formId);

        if (!$form) {
            return new failResource(['النموذج غير موجود.']);
        }

        if ($form->status !== $expectedCurrentStatus) {
            return new failResource([
                "الحالة الحالية للنموذج ليست \"{$expectedCurrentStatus->value}\"."
            ]);
        }

        $form->status = $newStatus->value;

        if (!$form->save()) {
            return new failResource(['فشل في حفظ التغييرات.']);
        }

        return new successResource(['new_status' => $form->status]);
    }
}
