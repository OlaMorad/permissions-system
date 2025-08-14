<?php

namespace App\Http\Requests;

use App\Models\Manager;
use Illuminate\Foundation\Http\FormRequest;

class UpdateManagerRequest extends FormRequest
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
    // جلب الـ user_id المرتبط بالـ employee_id من قاعدة البيانات
    $userId = null;

    if ($this->manager_id) {
        $manager = Manager::find($this->manager_id);
        $userId = $manager ? $manager->user_id : null;
    }

    return [
        'manager_id' => ['required', 'exists:Managers,id'],
        'name'        => ['nullable', 'string', 'max:255'],
        'email'       => [
            'nullable',
            'email',
            'max:255',
            // استثناء السجل الحالي من فحص التكرار
            'unique:users,email,' . $userId,
        ],
        'address'     => ['nullable', 'string', 'max:255'],
        'phone'       => [
            'nullable',
            'string',
            'regex:/^[0-9+\-\s]+$/',
            'min:6',
            'unique:users,phone,' . $userId,
        ],
        'password'    => ['nullable', 'string', 'min:8'],
        'avatar'      => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
    ];
}


}
