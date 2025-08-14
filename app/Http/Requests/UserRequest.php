<?php

namespace App\Http\Requests;

use App\Models\Manager;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'address'  => ['required', 'string', 'max:255'],
            'phone'    => ['required', 'string', 'regex:/^[0-9+\-\s]+$/', 'min:6','unique:users,phone'],
            'avatar'   => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:4096'], // 2MB
            'role_id'  => ['required', 'integer', 'exists:roles,id'],
        ];
    }
    public function messages(): array
    {
        return [
            // الاسم
            'name.required' => 'يرجى إدخال اسم الموظف',
            'name.string' => 'يجب أن يكون الاسم نصاً صحيحاً.',

            // البريد الإلكتروني
            'email.required' => 'أدخل البريد الالكتروني الخاص بهذا الموظف',
            'email.email' => 'يجب أن يكون البريد الإلكتروني بصيغة صحيحة.',
            'email.unique' => 'هذا البريد الإلكتروني مستخدم مسبقاً',

            // كلمة المرور
            'password.required' => 'ادخل كلمة المرور',
            'password.min' => 'يجب أن لا تقل كلمة المرور عن 8 محارف',

            // العنوان
            'address.required' => 'أدخل العنوان الخاص بالموظف',
            'address.string' => 'يجب أن يكون العنوان نصاً صحيحاً.',
            'address.max' => 'العنوان لا يجب أن يتجاوز 255 محرف.',

            // رقم الهاتف
            'phone.required' => 'أدخل رقم الهاتف الخاص بهذا الموظف',
            'phone.min' => 'رقم الهاتف يجب أن لا يقل عن 6 أرقام.',
            'phone.unique' => 'رقم الهاتف مستخدم مسبقاً',

            // الصورة الرمزية (Avatar)
            'avatar.required' => 'يرجى رفع صورة الموظف',
            'avatar.mimes' => 'يجب أن تكون صيغة الصورة jpeg أو png أو jpg أو gif أو svg.',
            'avatar.max' => 'حجم الصورة يجب ألا يتجاوز 4 ميغابايت.',

            // الدور
            'role_id.required' => 'حدد الدور الذي سيشغله الموظف',
            'role_id.exists' => 'الدور المحدد غير موجود في النظام',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $roleId = $this->input('role_id');
            $role = Role::find($roleId);

            if ($role && str_starts_with($role->name, 'رئيس')) {
                if (Manager::where('role_id', $role->id)->exists()) {
                    $validator->errors()->add('role_id', 'لا يمكن إنشاء أكثر من رئيس واحد لهذه الدائرة');
                }
            }
        });
    }
}
