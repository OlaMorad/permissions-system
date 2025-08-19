<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'old_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'old_password.required' => 'يرجى إدخال كلمة المرور القديمة.',
            'new_password.required' => 'يرجى إدخال كلمة المرور الجديدة.',
            'new_password.min' => 'كلمة المرور الجديدة يجب أن تكون على الأقل 8 محارف.',
            'new_password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
            'new_password_confirmation.required' => 'يرجى إدخال حقل تأكيد كلمة المرور.',

        ];
    }
}
