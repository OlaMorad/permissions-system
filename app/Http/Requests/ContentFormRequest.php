<?php

namespace App\Http\Requests;

use App\Enums\Element_Type;
use App\Models\FormElement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

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
        ];
    }

    public function messages(): array
    {
        return [
            'form_id.required' => 'حقل رقم النموذج مطلوب.',
            'form_id.exists' => 'النموذج المحدد غير موجود.',
            'elements.required' => 'يجب إدخال عناصر النموذج.',
            'elements.array' => 'صيغة العناصر غير صحيحة.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $formId = $this->input('form_id');

            if (!$formId || !is_numeric($formId)) return;

            $inputElements = $this->input('elements', []);
            $fileElements = $this->file('elements', []);

            $formElements = FormElement::where('form_id', $formId)->orderBy('id')->get();

            $groups = [];
            $currentGroup = [];
            $validTypes = [Element_Type::CHECKBOX->value, Element_Type::Multiple_Choice->value];

            foreach ($formElements as $element) {
                $type = $element->type->value;
                $label = $element->label;

                // اجمع عناصر المجموعة
                if (in_array($type, $validTypes)) {
                    $currentGroup[] = $element;
                } else {
                    // إذا انتهت مجموعة، خزّنها ثم أفرغها
                    if (!empty($currentGroup)) {
                        $groups[] = $currentGroup;
                        $currentGroup = [];
                    }

                    // تحقق من قيمة العنصر (نص أو ملف)
                    $value = $fileElements[$label] ?? $inputElements[$label] ?? null;
                    $this->validateElement($validator, $element, $value);
                }
            }

            // أضف المجموعة الأخيرة إن وُجدت
            if (!empty($currentGroup)) {
                $groups[] = $currentGroup;
            }

            // تحقق من كل مجموعة (يجب اختيار خيار واحد فقط)
            foreach ($groups as $group) {
                $selectedCount = 0;
                $labels = array_map(fn($el) => $el->label, $group);
                $groupType = $group[0]->type->value; // كل عناصر المجموعة من نفس النوع

                foreach ($group as $element) {
                    $label = $element->label;
                    $value = $inputElements[$label] ?? null;

                    if (!empty($value)) {
                        $selectedCount++;
                    }
                }

                if ($groupType === Element_Type::CHECKBOX->value) {
                    //  Checkbox: لازم خيار واحد بالضبط
                    if ($selectedCount !== 1) {
                        $validator->errors()->add(
                            'elements',
                            'يجب اختيار خيار واحد فقط من بين: ' . implode(' أو ', $labels)
                        );
                    }
                }

                if ($groupType === Element_Type::Multiple_Choice->value) {
                    //  Multiple Choice: لازم خيار واحد أو أكثر
                    if ($selectedCount < 1) {
                        $validator->errors()->add(
                            'elements',
                            'يجب اختيار خيار واحد على الأقل من بين: ' . implode(' أو ', $labels)
                        );
                    }
                }
            }
        });
    }

    protected function validateElement($validator, FormElement $element, $value): void
    {
        $label = $element->label;
        $type = $element->type->value;
        // تجاهل النصوص والعناوين
        if ($type === Element_Type::TEXT->value || $type === Element_Type::TITLE->value) {
            return;
        }
        if (is_null($value)) {
            $validator->errors()->add('elements', "العنصر ذو التسمية '{$label}' مفقود ويجب تعبئته.");
            return;
        }

        // تحقق من ملف مرفق
        if ($type === Element_Type::ATTACHED_FILE->value) {
            $this->validateUploadedFile($validator, $value, $label, ['pdf', 'doc', 'docx', 'xls', 'xlsx'], 'ملف');
            return;
        }

        // تحقق من صورة مرفقة
        if ($type === Element_Type::ATTACHED_IMAGE->value) {
            $this->validateUploadedFile($validator, $value, $label, ['jpg', 'jpeg', 'png'], 'صورة');
            return;
        }

        // تحقق من إدخال عادي
        $this->validateStandardInput($validator, $label, $value);
    }

    protected function validateStandardInput($validator, string $label, $value): void
    {
        if (is_null($value) || $value === '') {
            $validator->errors()->add('elements', "يجب إدخال قيمة للعنصر '{$label}'.");
        }
    }

    protected function validateUploadedFile($validator, $file, string $label, array $allowedExtensions, string $type): void
    {
        if (!($file instanceof UploadedFile) || !$file->isValid()) {
            $validator->errors()->add('elements', "الـ {$type} المرفق في '{$label}' غير صالح.");
            return;
        }

        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, $allowedExtensions)) {
            $validator->errors()->add(
                'elements',
                "نوع {$type} '{$label}' غير مسموح. الأنواع المسموحة: [" . implode(', ', $allowedExtensions) . "]"
            );
        }
    }
}
