<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IdInternalMailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                Rule::exists('internal_mails', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'رقم البريد مطلوب.',
            'id.integer' => 'رقم البريد يجب أن يكون رقماً.',
            'id.exists' => 'البريد غير موجود.',
        ];
    }
}
