<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class AnswerExamRequest extends FormRequest
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
            'exam_id' => 'required|exists:exams,id',
            'answers' => 'nullable|array',
            'answers.*.id' => [
                'required',
                'distinct', // لا يسمح بالتكرار
                'exists:exam_questions,id',
                function ($attribute, $value, $fail) {
                    $examId = $this->input('exam_id');
                    $belongs = DB::table('exam_questions')
                        ->where('id', $value)
                        ->where('exam_id', $examId)
                        ->exists();

                    if (!$belongs) {
                        $fail('السؤال المحدد لا ينتمي لهذا الامتحان.');
                    }
                },
            ],
            'answers.*.selected_option' => 'required|string|in:A,B,C,D',
        ];
    }
    public function messages(): array
    {
        return [
            'exam_id.required' => 'الرجاء تحديد الامتحان.',
            'exam_id.exists' => 'الامتحان المحدد غير موجود.',

            'answers.*.id.required' => 'يجب تحديد السؤال.',
            'answers.*.id.exists' => 'السؤال غير موجود.',
            'answers.*.id.distinct' => 'لا يمكنك تكرار نفس السؤال أكثر من مرة.',

            'answers.*.selected_option.required' => 'يرجى اختيار إجابة.',
            'answers.*.selected_option.in' => 'الخيارات المسموحة هي A أو B أو C أو D.',
        ];
    }

  
}
