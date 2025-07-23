<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DoctorRegisterRequest extends FormRequest
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
            'name' => ['required', 'regex:/^(\S+\s+){2,}\S+$/'],
            'phone' => ['required', 'unique:users,phone'],
            'email'=> ['required', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ];
    }

      public function messages(): array
    {
        return [
            'name.required' => 'حقل الاسم مطلوب.',
            'name.regex' => 'الرجاء إدخال الاسم الثلاثي (على الأقل 3 كلمات).',

            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.unique' => 'رقم الهاتف مستخدم بالفعل.',

              'email.required' => 'البريد الالكتروني  مطلوب.',
            'email.unique' => ' البريد الالكتروني مستخدم بالفعل.',

            'password.required' => 'كلمة المرور مطلوبة.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
            'password.min' => 'يجب أن تكون كلمة المرور 8 أحرف على الأقل.',
        ];
    }
}
