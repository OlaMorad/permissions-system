<?php

namespace App\Services;

use App\Models\Form;
use App\Http\Resources\successResource;
use App\Http\Resources\failResource;

class FormService
{
    public function UpdateStatus(int $formId)
    {
        $form = Form::find($formId);

        if (!$form) {
            return new failResource(['النموذج غير موجود.']);
        }

        $form->status = $form->status === 'active' ? 'inactive' : 'active';

        if (!$form->save()) {
            return new failResource(['فشل في حفظ التغييرات.']);
        }

        return new successResource([
            'new_status' => $form->status,
        ]);
    }
}
