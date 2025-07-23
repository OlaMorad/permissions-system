<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExamNameRequest extends FormRequest
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
            'exam_number' => 'required|exists:candidates,exam_number'
        ];
    }
    public function messages()
    {
        return [
            'exam_number.required' => 'الرقم الامتحاني مطلوب',
            'exam_number.exists' => 'الرقم الامتحاني غير صحيح'
        ];
    }
}
