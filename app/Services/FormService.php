<?php

namespace App\Services;

use App\Models\Form;
use App\Enums\FormStatus;
use App\Http\Resources\successResource;
use App\Http\Resources\failResource;

class FormService
{
    public function changeStatus(int $formId, FormStatus $expectedCurrentStatus, FormStatus $newStatus)
    {
        $form = Form::find($formId);

        if (!$form) {
            return new failResource(['النموذج غير موجود.']);
        }

        if ($form->status !== $expectedCurrentStatus) {

            return new failResource(["الحالة الحالية للنموذج ليست \"{$expectedCurrentStatus->value}\"."]);
        }

        $form->status = $newStatus->value;

        if (!$form->save()) {
            return new failResource(['فشل في حفظ التغييرات.']);
        }

        return new successResource(['new_status' => $form->status]);
    }
    public function changeUnderReviewToActive(int $formId)
    {
        return $this->changeStatus($formId, FormStatus::UNDER_REVIEW, FormStatus::Active);
    }

    public function changeActiveToInactive(int $formId)
    {
        return $this->changeStatus($formId, FormStatus::Active, FormStatus::Inactive);
    }

    public function changeInactiveToActive(int $formId)
    {
        return $this->changeStatus($formId, FormStatus::Inactive, FormStatus::Active);
    }
}

