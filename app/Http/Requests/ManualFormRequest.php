<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManualFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'elements' => ['required', 'array'],
            'elements.*.label' => ['required', 'string'],
            'elements.*.type' => ['required', 'integer'],
            'path_ids' => ['required', 'array'],
            'path_ids.*' => ['exists:paths,id'],
        ];
    }

    /**
     * رسائل التحقق.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم النموذج مطلوب.',
            'elements.required' => 'يجب إدخال عناصر النموذج.',
            'elements.*.label.required' => 'كل عنصر يجب أن يحتوي على تسمية.',
            'elements.*.type.required' => 'كل عنصر يجب أن يحتوي على نوع.',
            'elements.*.type.integer' => 'نوع الحقل يجب أن يكون رقماً صحيحاً.',
            'path_ids.required' => 'يجب اختيار مسار واحد على الأقل للنموذج.',
            'path_ids.array' => 'المسارات يجب أن تكون في شكل مصفوفة.',
            'path_ids.*.exists' => 'المسار المحدد غير موجود في قاعدة البيانات.',
        ];
    }
}
