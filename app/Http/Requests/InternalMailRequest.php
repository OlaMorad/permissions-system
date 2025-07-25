<?php

namespace App\Http\Requests;

use App\Enums\StatusInternalMail;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

class InternalMailRequest extends FormRequest
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
            'subject'      => 'required|string|max:255',
            'body'         => 'required|string',
            'to_path_ids'  => 'nullable|array'
        ];


    }

    public function messages(): array
{
    return [
        'subject.required' => 'حقل الموضوع مطلوب.',
        'subject.string'   => 'حقل الموضوع يجب أن يكون نصًا.',
        'subject.max'      => 'حقل الموضوع يجب ألا يتجاوز 255 حرفًا.',

        'body.required'    => 'حقل المحتوى مطلوب.',
        'body.string'      => 'حقل المحتوى يجب أن يكون نصًا.',

        'to_path_ids.array' => 'حقل جهات الاستلام يجب أن يكون مصفوفة.',
    ];
}

}
