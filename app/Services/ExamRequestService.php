<?php

namespace App\Services;

use App\Models\FormContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ExamRequestService{

 public function create_form_content_exam($data){


     return DB::transaction(function () use ($data) {
            $doctor = Auth::user()->doctor;

            $formContent = FormContent::create([
                'form_id' => $data['form_id'],
                'doctor_id' => $doctor->id,
            ]);

            $this->storeElementValues($formContent, $data['elements'] ?? []);
            $this->storeAttachments($formContent, $data['attachments'] ?? []);

            return $formContent;
        });
 }


     protected function storeElementValues(FormContent $formContent, array $elements): void
    {
        foreach ($elements as $label => $value) {
            $formElement = $formContent->form->elements->firstWhere('label', $label);
            if ($formElement) {
                $formContent->elementValues()->create([
                    'form_element_id' => $formElement->id,
                    'value' => $value,
                ]);
            }
        }
    }



protected function storeAttachments(FormContent $formContent, array $media): void
{
    foreach ($media as $label => $files) {
        // تأكدي أن الملفات مصفوفة
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                // خزني الملف واحصلي على المسار
                $path = $file->store("exam_attachments/files", 'public');

                //نبحث عن الليبل المدخل في جدول الايلمنت
                $formElement = $formContent->form->elements->firstWhere('label', $label);
                if ($formElement) {
                    $formContent->elementValues()->create([
                        'form_element_id' => $formElement->id,
                        'value' => $path,
                    ]);
                }
            }
        }
    }
}


}
