<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExamRequest extends FormRequest
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
            'form_id' => ['required', 'exists:forms,id'],
            'elements' => ['required', 'array'],
            'attachments' => ['nullable', 'array'],
            'attachments.file' => ['nullable'],
            'attachments.file.*' => ['file', 'mimes:pdf,doc,docx,txt', 'max:2048'],
        ];
    }
}
