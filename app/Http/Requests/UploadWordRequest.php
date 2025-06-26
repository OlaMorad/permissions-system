<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadWordRequest extends FormRequest
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
            'file' => ['required', 'file', 'mimes:docx'],
            'cost' => ['required', 'numeric', 'min:0'],
            'path_ids' => ['required', 'array'],
            'path_ids.*' => ['exists:paths,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'يجب رفع ملف Word',
            'file.mimes'    => 'يجب أن يكون الملف بصيغة docx',
            'cost.required' => 'يرجى تحديد كلفة المعاملة.',
            'cost.min'      => 'لا يمكن أن تكون الكلفة سالبة.',
            'path_ids.required' => 'يجب اختيار مسارات للنموذج.',
            'path_ids.array' => 'يجب إرسال المسارات كمصفوفة.',
            'path_ids.*.exists' => 'أحد المسارات غير موجود في قاعدة البيانات.',
        ];
    }
}
