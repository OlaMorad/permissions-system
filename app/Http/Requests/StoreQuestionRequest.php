<?php

namespace App\Http\Requests;

use App\Enums\OptionEnum;
use App\Enums\DifficultyLevelEnum;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
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
            'question' => 'required|string',
            'specialization_id' => 'required|exists:specializations,id',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_answer' => 'required', new Enum(OptionEnum::class),
            'difficulty_level' => 'required' , new Enum(DifficultyLevelEnum::class),
        ];
    }
        public function messages(): array
    {
        return [
            'question.required'           => 'يرجى إدخال نص السؤال.',
            'specialization_id.required' => 'يرجى اختيار الاختصاص.',
            'specialization_id.exists'   => 'الاختصاص غير موجود في النظام.',
            'option_a.required'          => 'يرجى إدخال الخيار A.',
            'option_b.required'          => 'يرجى إدخال الخيار B.',
            'option_c.required'          => 'يرجى إدخال الخيار C.',
            'option_d.required'          => 'يرجى إدخال الخيار D.',
            'correct_answer.required'    => 'يرجى تحديد الجواب الصحيح.',
            'difficulty_level.required'  => 'يرجى تحديد مستوى الصعوبة.',
        ];
    }

}
