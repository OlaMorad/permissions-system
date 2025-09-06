<?php

namespace App\Http\Requests;

use App\Enums\ExamRequestEnum;
use App\Models\Program;
use App\Models\Specialization;
use Carbon\Carbon;
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
            'exams.*.simple_ratio' => 'required|numeric|min:1|max:99',
            'exams.*.average_ratio' => 'required|numeric|min:1|max:99',
            'exams.*.hard_ratio' => 'required|numeric|min:1|max:99',
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
            'exams.*.day.in' => 'يجب أن يكون يوم الامتحان مكتوباً باللغة العربية',
            'exams.*.date.required' => 'التاريخ مطلوب لكل امتحان.',
            'exams.*.date.date' => 'التاريخ غير صالح.',
            'exams.*.simple_ratio.required' => 'يرجى إدخال نسبة الأسئلة البسيطة',
            'exams.*.simple_ratio.numeric' => 'نسبة الأسئلة البسيطة يجب أن تكون رقماً',
            'exams.*.simple_ratio.min' => 'نسبة الأسئلة البسيطة يجب أن تكون أكبر من صفر',
            'exams.*.simple_ratio.max' => 'نسبة الأسئلة البسيطة يجب أن لا تتجاوز ال 100',
            'exams.*.average_ratio.required' => 'يرجى إدخال نسبة الأسئلة المتوسطة',
            'exams.*.average_ratio.numeric' => 'نسبة الأسئلة المتوسطة يجب أن تكون رقماً',
            'exams.*.average_ratio.min' => 'نسبة الأسئلة المتوسطة يجب أن تكون أكبر من صفر',
            'exams.*.average_ratio.max' => 'نسبة الأسئلة المتوسطة يجب أن لا تتجاوز ال 100',
            'exams.*.hard_ratio.required' => 'يرجى إدخال نسبة الأسئلة الصعبة',
            'exams.*.hard_ratio.numeric' => 'نسبة الأسئلة الصعبة يجب أن تكون رقماً',
            'exams.*.hard_ratio.min' => 'نسبة الأسئلة الصعبة يجب أن تكون أكبر من صفر',
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
            $monthName = $this->input('month');
            $year = $this->input('year');
            // التحقق من عدم تكرار برنامج لنفس الشهر والسنة إذا لم يكن مرفوضاً
            $existingProgram = Program::where('month', $monthName)
                ->where('year', $year)
                ->where('approved', '!=', ExamRequestEnum::REJECTED->value)
                ->first();

            if ($existingProgram) {
                $validator->errors()->add('month', 'يوجد بالفعل برنامج لهذا الشهر والسنة ولم يتم رفضه . لا يمكن إنشاء برنامج جديد إلا بعد رفض البرنامج السابق.');
            }

            // خريطة أسماء الأشهر لأرقام الشهور الميلادية
            $monthsMap = [
                'نيسان' => 4,
                'تشرين الأول' => 10,
            ];
            $monthNumber = $monthsMap[$monthName] ?? null;
            $now = Carbon::now();

            if ($monthNumber) {
                // حساب آخر وقت مسموح لإنشاء البرنامج: 31 يوم قبل بداية الشهر المدخل
                $allowed_created_at = Carbon::create($year, $monthNumber, 1)
                    ->subDays(31)
                    ->endOfDay();

                if ($now->gt($allowed_created_at)) {
                    $validator->errors()->add('created_at', 'يجب إنشاء البرنامج قبل 31 يومًا على الأقل من بداية الشهر والسنة المدخلة.');
                }
                // بناء مصفوفة الأشهر الخمسة المتتالية مع مراعاة السنة
                $allowed_months = [];
                for ($i = 0; $i < 5; $i++) {
                    $month = $monthNumber + $i;
                    $start_year = $year;
                    if ($month > 12) {
                        $month -= 12;
                        $start_year += 1;
                    }
                    $allowed_months[] = ['month' => $month, 'year' => $start_year];
                }

                // خريطة الأيام بالعربية إلى الإنجليزية
                $arabicDaysMap = [
                    'الأحد'     => 'Sunday',
                    'الاثنين'   => 'Monday',
                    'الثلاثاء'  => 'Tuesday',
                    'الأربعاء'  => 'Wednesday',
                    'الخميس'    => 'Thursday',
                    'الجمعة'    => 'Friday',
                    'السبت'     => 'Saturday',
                ];

                // تحقق من تواريخ الامتحانات وتطابق اليوم مع التاريخ
                foreach ($exams as $index => $exam) {
                    $examDate = Carbon::parse($exam['date']);
                    $examMonth = $examDate->month;
                    $examYear = $examDate->year;

                    // التحقق من أن تاريخ الامتحان يقع ضمن الأشهر المسموح بها
                    $valid = false;
                    foreach ($allowed_months as $allowed) {
                        if ($examMonth === $allowed['month'] && $examYear === $allowed['year']) {
                            $valid = true;
                            break;
                        }
                    }
                    if (!$valid) {
                        $validator->errors()->add(
                            "exams.$index.date",
                            "تاريخ الامتحان يجب أن يكون ضمن الفترة الزمنية المعتمدة في برنامج الامتحان، ولا يتجاوز بداية الدورة الامتحانية القادمة."
                        );
                    }

                    // التحقق من تطابق اليوم مع التاريخ
                    $expectedDayEn = $examDate->format('l'); // Sunday, Monday...
                    $providedDayAr = $exam['day'] ?? null;

                    if (
                        $providedDayAr &&
                        isset($arabicDaysMap[$providedDayAr]) &&
                        $arabicDaysMap[$providedDayAr] !== $expectedDayEn
                    ) {
                        $correctArabicDay = array_search($expectedDayEn, $arabicDaysMap);
                        $validator->errors()->add(
                            "exams.$index.day",
                            "اليوم المدخل ({$providedDayAr}) لا يتطابق مع تاريخ الامتحان ({$exam['date']})، حيث يصادف يوم {$correctArabicDay}."
                        );
                    }
                }
                //  التحقق من مجموع النسب لكل امتحان
                foreach ($exams as $index => $exam) {
                $total = array_sum([
                    $exam['simple_ratio'] ?? 0,
                    $exam['average_ratio'] ?? 0,
                    $exam['hard_ratio'] ?? 0,
                ]);
                if ($total !== 100) {
                    $validator->errors()->add(
                        "exams.$index",
                        " %مجموع نسب الأسئلة البسيطة و المتوسطة و الصعبة يجب أن يكون 100"
                    );
                }
            }
            //  التحقق من عدد الامتحانات = عدد الاختصاصات
            $specializationCount = Specialization::where('status', ExamRequestEnum::APPROVED->value)->count();
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
    }});
    }
}

