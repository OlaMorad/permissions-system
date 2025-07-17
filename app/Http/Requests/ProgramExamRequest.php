<?php

namespace App\Http\Requests;

use App\Models\Specialization;
use Illuminate\Foundation\Http\FormRequest;

class ProgramExamRequest extends FormRequest
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
            'month' => 'required|string|in:نيسان,تشرين الأول',
            'year' => 'required|integer',
            'exams' => 'required|array|min:1',
            'exams.*.specialization_id' => 'required|exists:specializations,id',
            'exams.*.day' => 'required|string|in:الجمعة,السبت,الأحد,الاثنين,الثلاثاء,الأربعاء,الخميس',
            'exams.*.date' => 'required|date',
            'exams.*.simple_ratio' => 'required|numeric|min:0|max:100',
            'exams.*.average_ratio' => 'required|numeric|min:0|max:100',
            'exams.*.hard_ratio' => 'required|numeric|min:0|max:100',
            'exams.*.start_time' => 'required|date_format:H:i',
            'exams.*.end_time' => 'required|date_format:H:i|after:exams.*.start_time',
        ];
    }

    public function messages(): array
    {
        return [
            'month.required' => 'قم بتحديد الشهر',
            'month.string' => 'حقل الشهر يجب أن يكون نصاً.',
            'month.in' => 'الشهر يجب أن يكون مكتوباً باللغة العربية ومن ضمن الأشهر المعتمدة',
            'year.required' => 'قم بتحديد السنة',
            'year.integer' => 'حقل السنة يجب أن يكون رقماً صحيحاً.',

            'exams.required' => 'يجب إدخال امتحان واحد على الأقل.',
            'exams.array' => 'يجب أن تكون الامتحانات على شكل مصفوفة.',
            'exams.*.specialization_id.required' => 'يرجى إدخال الاختصاص ',
            'exams.*.specialization_id.exists' => 'الاختصاص غير موجود.',
            'exams.*.day.required' => 'يرجى إدخال يوم الامتحان',
            'exams.*.day.in' => 'يجب أن يكون يوم الامتحان باللغة العربية و عدا ايام الجمعة و السبت',
            'exams.*.date.required' => 'التاريخ مطلوب لكل امتحان.',
            'exams.*.date.date' => 'التاريخ غير صالح.',
            'exams.*.simple_ratio.required' => 'يرجى إدخال نسبة الأسئلة البسيطة',
            'exams.*.simple_ratio.numeric' => 'نسبة الأسئلة البسيطة يجب أن تكون رقماً',
            'exams.*.simple_ratio.min' => 'نسبة الأسئلة البسيطة يجب أن تكون على الأقل صفر',
            'exams.*.simple_ratio.max' => 'نسبة الأسئلة البسيطة يجب أن لا تتجاوز ال 100',
            'exams.*.average_ratio.required' => 'يرجى إدخال نسبة الأسئلة المتوسطة',
            'exams.*.average_ratio.numeric' => 'نسبة الأسئلة المتوسطة يجب أن تكون رقماً',
            'exams.*.average_ratio.min' => 'نسبة الأسئلة المتوسطة يجب أن تكون على الأقل صفر',
            'exams.*.average_ratio.max' => 'نسبة الأسئلة المتوسطة يجب أن لا تتجاوز ال 100',
            'exams.*.hard_ratio.required' => 'يرجى إدخال نسبة الأسئلة الصعبة',
            'exams.*.hard_ratio.numeric' => 'نسبة الأسئلة الصعبة يجب أن تكون رقماً',
            'exams.*.hard_ratio.min' => 'نسبة الأسئلة الصعبة يجب أن تكون على الأقل صفر',
            'exams.*.hard_ratio.max' => 'نسبة الأسئلة الصعبة يجب أن لا تتجاوز ال 100',
            'exams.*.start_time.required' => 'قم بإدخال وقت بدء الامتحان',
            'exams.*.start_time.date_format' => 'وقت بدء الامتحان يجب أن يكون بالتنسيق (ساعة:دقيقة).',
            'exams.*.end_time.required' => 'قم بإدخال وقت انتهاء الامتحان',
            'exams.*.end_time.date_format' => 'وقت النهاية يجب أن يكون بالتنسيق (ساعة:دقيقة).',
            'exams.*.end_time.after' => 'وقت انتهاء الامتحان يجب أن يكون بعد وقت بدء الامتحان',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $exams = $this->input('exams', []);

            //  التحقق من مجموع النسب لكل امتحان
            foreach ($exams as $index => $exam) {
                if (array_sum([
                    $exam['simple_ratio'] ?? 0,
                    $exam['average_ratio'] ?? 0,
                    $exam['hard_ratio'] ?? 0,
                ]) > 100) {
                    $validator->errors()->add(
                        "exams.$index",
                        " %مجموع نسب الأسئلة البسيطة و المتوسطة و الصعبة يجب أن لا يتجاوز 100"
                    );
                }
            }

            //  التحقق من عدد الامتحانات = عدد الاختصاصات
            $specializationCount = Specialization::count();
            if (count($exams) !== $specializationCount) {
                $validator->errors()->add(
                    'exams',
                    "يجب أن يحتوي البرنامج على امتحان واحد لكل اختصاص. عدد الاختصاصات الحالي هو ($specializationCount)."
                );
            }

            //  التحقق من تكرار الاختصاصات ضمن الامتحانات
            $specializationIds = array_column($exams, 'specialization_id');
            if (count($specializationIds) !== count(array_unique($specializationIds))) {
                $validator->errors()->add(
                    'exams',
                    "لا يجب تكرار نفس الاختصاص في أكثر من امتحان ضمن نفس البرنامج"
                );
            }
        });
    }
}
