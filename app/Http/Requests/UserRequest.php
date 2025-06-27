<?php

namespace App\Http\Requests;

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
            'avatar'   => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // 2MB
        ];
    }
}
