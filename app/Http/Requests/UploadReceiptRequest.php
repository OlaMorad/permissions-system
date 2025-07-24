<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadReceiptRequest extends FormRequest
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
            'uuid' => ['required', 'uuid', 'exists:transactions,uuid'],
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png'],
        ];
    }
    public function messages(): array
    {
        return [
            'uuid.required' => 'حقل uuid مطلوب.',
            'uuid.uuid' => 'الـ uuid يجب أن يكون بتنسيق صحيح.',
            'uuid.exists' => 'لا توجد معاملة بهذا الـ uuid.',

            'receipt.required' => 'يرجى إرفاق إيصال الدفع.',
            'receipt.file' => 'الملف المرفق غير صالح.',
            'receipt.mimes' => 'صيغة الإيصال يجب أن تكون: jpg، jpeg، png',
        ];
    }
}
