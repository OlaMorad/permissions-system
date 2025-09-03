<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class UpdateQuestionStatusRequest extends FormRequest
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
            'question_id' => [
                'nullable',
                'integer',
                Rule::exists('question_banks', 'id')
            ],
            'batch_id' => [
                'nullable',
                'string',
                Rule::exists('question_banks', 'batch_id')
            ],
            'status' => [
                'required',
                Rule::in(array_column(\App\Enums\QuestionBankEnum::cases(), 'value'))
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // لازم يكون واحد فقط موجود: إما question_id أو batch_id
            if (!$this->filled('question_id') && !$this->filled('batch_id')) {
                $validator->errors()->add('question_id', 'يجب إدخال question_id أو batch_id.');
                $validator->errors()->add('batch_id', 'يجب إدخال question_id أو batch_id.');
            }

            if ($this->filled('question_id') && $this->filled('batch_id')) {
                $validator->errors()->add('question_id', 'لا يمكن إدخال question_id و batch_id معاً.');
                $validator->errors()->add('batch_id', 'لا يمكن إدخال question_id و batch_id معاً.');
            }
        });
    }

}
