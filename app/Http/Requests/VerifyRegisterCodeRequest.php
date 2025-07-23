<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyRegisterCodeRequest extends FormRequest
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
            'code' => 'required|exists:email_verifications,code',
            'email' => 'required|exists:email_verifications,email'
        ];
    }

    public function messages(){
        return [
          'code.required'=>'كود التحقق مطلوب',
          'code.exists'=>'كود التحقق غير صحيح',
          'email.required'=>'الايميل الالكتروني مطلوب',
          'email.exists'=>'الايميل الالكتروني غير صحيح'
        ];
    }
}
