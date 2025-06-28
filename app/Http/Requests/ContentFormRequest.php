<?php

namespace App\Http\Requests;

use App\Enums\Element_Type;
use App\Models\FormElement;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ContentFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'form_id' => ['required', 'exists:forms,id'],
            'elements' => ['required', 'array'],
            'media.receipt' => ['required', 'file', 'mimes:jpg,jpeg,png'],
            'media.*.file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
            'media.*.image' => ['nullable', 'file', 'mimes:jpg,jpeg,png'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $formId = $this->input('form_id');
            $elementsInput = $this->input('elements') ?? [];

            // تأكد من أن form_id صالح لتفادي الأخطاء
            if (!$formId || !is_numeric($formId)) return;

            $requiredElements = FormElement::where('form_id', $formId)->get();

            foreach ($requiredElements as $element) {
                $this->validateElement($validator, $element, $elementsInput);
            }
        });
    }

    /**
     * التحقق من عنصر فردي بناءً على نوعه.
     */
    protected function validateElement($validator, FormElement $element, array $inputElements): void
    {
        $label = $element->label;
        $type = $element->type instanceof Element_Type
            ? $element->type->value
            : (int) $element->type;

        // تجاوز التحقق لعناصر الملفات/الصور
        if (in_array($type, [
            Element_Type::ATTACHED_FILE->value,
            Element_Type::ATTACHED_IMAGE->value,
        ])) {
            return;
        }

        // تحقق من وجود العنصر
        if (!array_key_exists($label, $inputElements)) {
            $validator->errors()->add(
                'elements',
                "العنصر ذو التسمية '{$label}' مفقود ويجب تعبئته."
            );
            return;
        }

        $value = $inputElements[$label];

        // تحقق من نوع CHECKBOX
        if ($type === Element_Type::CHECKBOX->value) {
            $this->validateCheckbox($validator, $label, $value);
        } else {
            $this->validateStandardInput($validator, $label, $value);
        }
    }

    /**
     * التحقق من صحة بيانات checkbox.
     */
    protected function validateCheckbox($validator, string $label, $value): void
    {
        $rawOptions = explode('☐', $label);
        $allowedOptions = collect($rawOptions)
            ->map(fn($v) => trim($v))
            ->filter()
            ->values()
            ->all();

        if (!in_array($value, $allowedOptions)) {
            $validator->errors()->add(
                'elements',
                "القيمة المختارة '{$value}' غير مسموحة لحقل '{$label}'. القيم المسموحة: [" . implode(', ', $allowedOptions) . "]"
            );
        }
    }

    /**
     * التحقق من القيم النصية العادية.
     */
    protected function validateStandardInput($validator, string $label, $value): void
    {
        if (is_null($value) || $value === '') {
            $validator->errors()->add(
                'elements',
                "يجب إدخال قيمة للعنصر '{$label}'."
            );
        }
    }
}
