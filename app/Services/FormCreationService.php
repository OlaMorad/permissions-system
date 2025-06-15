<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormElement;
use App\Interfaces\FormInputInterface;

class FormCreationService
{
    public function create_Form(FormInputInterface $inputService, string $formName, array $pathIds = []): Form
    {
        $form = Form::create([
            'name' => pathinfo($formName, PATHINFO_FILENAME),
        ]);

        foreach ($inputService->extractElements() as $element) {
            FormElement::create([
                'form_id' => $form->id,
                'label' => $element['label'],
                'type' => $element['type'],
            ]);
        }
        // ربط الفورم بالمسارات
        $form->paths()->sync($pathIds);
        return $form;
    }
}
