<?php

namespace App\Services;

use App\Enums\Element_Type;
use App\Models\FormContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;

class FormContentService
{
    public function createFormContent(array $data)
    {
        return DB::transaction(function () use ($data) {
            $doctor = Auth::user()->doctor;

            $formContent = FormContent::create([
                'form_id' => $data['form_id'],
                'doctor_id' => $doctor->id,
            ]);

            $this->storeElementValuesAndMedia($formContent, $data['elements'] ?? []);

            return $formContent;
        });
    }

    protected function storeElementValuesAndMedia(FormContent $formContent, array $elements): void
    {
        foreach ($elements as $label => $value) {
            $formElement = $formContent->form->elements->firstWhere('label', $label);
            if (!$formElement) continue;

            $type = $formElement->type->value;

            if (in_array($type, [Element_Type::ATTACHED_IMAGE->value, Element_Type::ATTACHED_FILE->value])) {
                $storedPath = $this->storeFileIfExists($value, $type === Element_Type::ATTACHED_IMAGE->value ? 'images' : 'files');

                $formContent->media()->create([
                    'form_element_id' => $formElement->id,
                    'image' => $type === Element_Type::ATTACHED_IMAGE->value ? $storedPath : null,
                    'file' => $type === Element_Type::ATTACHED_FILE->value ? $storedPath : null,
                ]);
            } else {
                $formContent->elementValues()->create([
                    'form_element_id' => $formElement->id,
                    'value' => $value,
                ]);
            }
        }
    }

    protected function storeFileIfExists($file, $directory)
    {
        if ($file instanceof UploadedFile) {
            return $file->store($directory, 'public');
        }
        return null;
    }
}
