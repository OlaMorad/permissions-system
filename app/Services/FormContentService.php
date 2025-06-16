<?php

namespace App\Services;

use App\Models\FormContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FormContentService
{
    public function create_form_content(array $data)
    {
        return DB::transaction(function () use ($data) {
            // dd(Auth::user()->id);
            // إنشاء سجل تعبئة النموذج
            $doctor = Auth::user()->doctor;
            // dd($doctor->id);
            $formContent = FormContent::create([
                'form_id' => $data['form_id'],
                'doctor_id' => $doctor->id,
            ]);

            // حفظ القيم المرتبطة بالعناصر
            foreach ($data['elements'] as $element) {
                if (isset($element['form_element_id'])) {
                    $formContent->elementValues()->create([
                        'form_element_id' => $element['form_element_id'],
                        'value' => $element['value'] ?? null,
                    ]);
                }
            }

            // حفظ الوسائط إن وجدت
            if (!empty($data['media']) && is_array($data['media'])) {
                foreach ($data['media'] as $media) {
                    $formContent->media()->create([
                        'file_path' => $media['file_path'] ?? null,
                        'image_path' => $media['image_path'] ?? null,
                    ]);
                }
            }

            return $formContent;
        });
    }
}
