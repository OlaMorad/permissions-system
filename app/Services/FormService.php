<?php

namespace App\Services;

use App\Models\Form;
use App\Enums\FormStatus;
use App\Http\Resources\successResource;
use App\Http\Resources\failResource;

class FormService
{
    // تغيير الحالة من قيد الدراسة إلى فعالة أو مرفوضة
    public function changeUnderReviewStatus(int $formId, FormStatus $targetStatus)
    {
        if (!in_array($targetStatus, [FormStatus::Active, FormStatus::REJECTED])) {
            return new failResource(['الحالة غير مسموح بها. فقط "فعالة" أو "مرفوضة".']);
        }
        return $this->changeStatus($formId,FormStatus::UNDER_REVIEW,$targetStatus);
    }

    // تبديل حالة النموذج بين فعالة وغير فعالة فقط.

    public function toggleActiveStatus(int $formId)
    {
        $form = Form::findOrFail($formId);

        if ($form->status === FormStatus::Active) {
            return $this->changeStatus($formId, FormStatus::Active, FormStatus::Inactive);
        } elseif ($form->status === FormStatus::Inactive) {
            return $this->changeStatus($formId, FormStatus::Inactive, FormStatus::Active);
        } else {
            return new failResource([
                'لا يمكن التبديل إلا بين الحالات: فعالة أو غير فعالة.'
            ]);
        }
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
