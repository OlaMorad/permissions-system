<?php

namespace App\Http\Requests;

use App\Enums\Element_Type;
use App\Models\FormElement;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ContentFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'form_id' => ['required', 'exists:forms,id'],

            'elements' => ['required', 'array'],
            'elements.*.form_element_id' => [
                'required',
                Rule::exists('form_elements', 'id')->where(function ($query) {
                    $query->where('form_id', $this->input('form_id'));
                }),
            ],
            'elements.*.value' => ['nullable'],

            'media' => ['nullable', 'array'],
            'media.*.file_path' => ['nullable', 'string'],
            'media.*.image_path' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $formId = $this->input('form_id');
            $inputElements = collect($this->input('elements') ?? []);

            // اجلب العناصر المطلوبة للنموذج
            $requiredElements = FormElement::where('form_id', $formId)->get();

            foreach ($requiredElements as $element) {
                $match = $inputElements->firstWhere('form_element_id', $element->id);

                if (!$match) {
                    $validator->errors()->add(
                        'elements',
                        "العنصر ذو التسمية '{$element->label}' مفقود ويجب تعبئته."
                    );
                    continue;
                }

                // القيمة المدخلة من المستخدم
                $value = $match['value'] ?? null;

                // إن كان CHECKBOX
                if ($element->type === Element_Type::CHECKBOX->value) {
                    if (!is_string($value) || empty($value)) {
                        $validator->errors()->add(
                            'elements',
                            "العنصر '{$element->label}' من نوع checkbox ويجب اختيار خيار واحد."
                        );
                        continue;
                    }

                    // استخرج القيم المسموحة
                    $rawOptions = explode('☐', $element->label);
                    $allowedOptions = collect($rawOptions)
                        ->map(fn($item) => trim($item))
                        ->filter()
                        ->values()
                        ->all();

                    // تحقق إذا القيمة المدخلة من القيم المسموحة
                    if (!in_array($value, $allowedOptions)) {
                        $validator->errors()->add(
                            'elements',
                            "القيمة المختارة '{$value}' غير مسموحة لحقل '{$element->label}'. القيم المسموحة هي: [" . implode(', ', $allowedOptions) . "]"
                        );
                    }
                } else {
                    // تحقق من وجود قيمة للعناصر غير الـ checkbox
                    if (is_null($value) || $value === '') {
                        $validator->errors()->add(
                            'elements',
                            "يجب إدخال قيمة للعنصر '{$element->label}'."
                        );
                    }
                }
            }
        });
    }
}
