<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormElement;
use App\Interfaces\FormInputInterface;
use App\Models\Path;

class FormCreationService
{
    public function create_Form(FormInputInterface $inputService, string $formName, float $cost, array $pathIds = []): Form
    {
        $form = Form::create([
            'name' => pathinfo($formName, PATHINFO_FILENAME),
            'cost' => $cost,
        ]);

        foreach ($inputService->extractElements() as $element) {
            FormElement::create([
                'form_id' => $form->id,
                'label' => $element['label'],
                'type' => $element['type'],
            ]);
        }

        // إجبار أول مسارين: المالية والديوان
        $fixedPaths = [
            Path::where('name', 'المالية')->first()?->id,
            Path::where('name', 'الديوان')->first()?->id,
        ];

        // دمجهم مع بقية المسارات
        $allPaths = array_unique(array_merge($fixedPaths, $pathIds));
        $form->paths()->sync($allPaths);

        return $form->fresh();
    }
}
