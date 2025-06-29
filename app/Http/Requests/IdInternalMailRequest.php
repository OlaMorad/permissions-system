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
            'uuid' => [
                'required',
                Rule::exists('internal_mails','uuid'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'uuid.required' => 'رقم البريد مطلوب.',
            'uuid.exists' => 'البريد غير موجود.',
        ];
    }
}
