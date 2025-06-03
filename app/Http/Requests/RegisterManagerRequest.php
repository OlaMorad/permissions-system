<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RegisterManagerRequest extends FormRequest
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
            'role_id' => [
                'required',
                'integer',
                Rule::in($this->ManagerRoleIds()),
                Rule::unique('managers', 'role_id'),
            ],
        ];
    }

    private function ManagerRoleIds(): array
    {
        return Role::where('name', 'like', 'Head of%')->pluck('id')->toArray();
    }

    public function messages(): array
    {
        return [
            'role_id.unique' => 'هذا الدور مخصص لرئيس قسم موجود بالفعل.',
            'role_id.in' => 'يمكنك اختيار دور من ضمن رؤساء الأقسام فقط.',
        ];
    }
    protected function prepareForValidation(): void
    {
        if ($this->route('role_id')) {
            $this->merge([
                'role_id' => $this->route('role_id'),
            ]);
        }
    }
}

