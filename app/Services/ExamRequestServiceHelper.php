<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Exam;
use App\Models\Form;
use App\Models\Program;
use App\Models\ExamRequest;
use App\Models\FormContent;
use App\Enums\ExamRequestEnum;
use App\Models\Specialization;


class ExamRequestServiceHelper
{

    //للتحقق من ما اذا تم ارسال طلب اعتذار سابق بنفس المواصفات
    public function hasPreviousApologyRequest(int $doctorId, string $year, string $cycle, int $formId, string $specialization): bool
    {
        return FormContent::where('doctor_id', $doctorId)
            ->where('form_id', $formId)
            ->whereHas('elementValues', function ($query) use ($year) {
                $query->whereHas('formElement', function ($q) {
                    $q->where('label', 'like', '%سنة%');
                })->where('value', $year);
            })
            ->whereHas('elementValues', function ($query) use ($cycle) {
                $query->whereHas('formElement', function ($q) use ($cycle) {
                    $q->where('label', 'like', '%دورة%');
                })->where('value', $cycle);
            })
            ->whereHas('elementValues', function ($query) use ($specialization) {
                $query->whereHas('formElement', function ($q) {
                    $q->where('label', 'like', '%اختصاص%');
                })->where('value', $specialization);
            })
            ->exists();
    }

    // لحتى نوقف استقبال الطلبات بالنسبة للاختصاص اذا ضل للامتحان اسبوع
    public function isExamTooClose(string $specializationName): bool
    {
        $specialization = Specialization::where('name', $specializationName)->first();

        if (!$specialization) {
            return false;
        }

        $today = Carbon::today();

        $exam = Exam::where('specialization_id', $specialization->id)
            ->whereDate('date', '>=', $today)
             ->latest('date')
            ->first();

        if (!$exam) {
            return false;
        }

        return Carbon::parse($today)->diffInDays($exam->date) <= 7;
    }

    // تحقق وجود طلب ترشيح سابق بنفس المواصفات
    public function checkNominationRequestPrecondition(int $formId, int $doctorId, int $specializationId, string $year, string $cycle): void
    {
        $formName = Form::find($formId)?->name;

        if (!$formName) {
            throw new \Exception('النموذج غير موجود.');
        }
        $specializationName = Specialization::find($specializationId)?->name;

        if (!$specializationName) {
            throw new \Exception('الاختصاص غير موجود.');
        }
        if (str_contains($formName, 'طلب اعتذار')) {

            // تحقق وجود طلب ترشيح سابق بنفس الطبيب، الاختصاص، السنة، والدورة
            $hasPreviousNomination = ExamRequest::whereHas('formContent', function ($query) use ($doctorId, $specializationName, $year, $cycle) {
                $query->where('doctor_id', $doctorId)
                    ->whereHas('form', function ($formQuery) {
                        $formQuery->where('name', 'like', '%طلب ترشيح%');
                    })
                    ->whereHas('elementValues', function ($q) use ($specializationName) {
                        $q->where('value', $specializationName)
                            ->whereHas('formElement', function ($q2) {
                                $q2->where('label', 'like', '%اختصاص%');
                            });
                    })
                    ->whereHas('elementValues', function ($q) use ($year) {
                        $q->where('value', $year)
                            ->whereHas('formElement', function ($q2) {
                                $q2->where('label', 'like', '%سنة%');
                            });
                    })
                    ->whereHas('elementValues', function ($q) use ($cycle) {
                        $cycleLabel = $cycle === 'نيسان' ? 'نيسان' : 'تشرين الأول';
                        $q->where('value', 'on')
                            ->whereHas('formElement', function ($q2) use ($cycleLabel) {
                                $q2->where('label', $cycleLabel);
                            });
                    });
            })->exists();
            if (!$hasPreviousNomination) {
                throw new \Exception('لا يمكنك إرسال طلب اعتذار قبل تقديم طلب ترشيح لنفس السنة، الدورة، والاختصاص.');
            }
        }
    }

    //للتتحقق ان الدكتور نفسو ما قدر يبعت اكتر من طلب لنفس الدورة و السنة
    public function hasSubmittedForSameSession(int $doctorId, $year, $cycle, int $formId, $specialization): bool
    {

        return FormContent::where('doctor_id', $doctorId)
            ->where('form_id', $formId)
            ->whereHas('elementValues', function ($query) use ($year) {
                $query->whereHas('formElement', function ($q) {
                    $q->where('label', 'like', '%سنة%')
                        ->orWhere('label', 'like', '%السنة%');
                })->where('value', $year);
            })

            ->whereHas('elementValues', function ($query) use ($cycle) {
                $query->whereHas('formElement', function ($q) use ($cycle) {
                    if ($cycle === 'نيسان' || $cycle === 'تشرين الأول') {
                        $q->where('label', $cycle);
                    } else {
                        $q->where('label', 'like', '%دورة%');
                    }
                })->where('value', $cycle === 'نيسان' || $cycle === 'تشرين الأول' ? 'on' : $cycle);
            })
            ->whereHas('elementValues', function ($query) use ($specialization) {
                $query->whereHas('formElement', function ($q) {
                    $q->where('label', 'like', '%اختصاص%');
                })->where('value', $specialization);
            })
            ->exists();
    }

    public function extractCycle(array $elements): ?string
    {
        if (isset($elements['نيسان'])) {
            return 'نيسان';
        }

        if (isset($elements['تشرين الأول'])) {
            return 'تشرين الأول';
        }

        if (isset($elements['دورة'])) {
            $cycles = $elements['دورة'];

            // إذا كانت قيمة الدورة عبارة عن مصفوفة
            if (is_array($cycles)) {
                foreach ($cycles as $cycle) {
                    if (stripos($cycle, 'نيسان') !== false) {
                        return 'نيسان';
                    }
                    if (stripos($cycle, 'تشرين') !== false) {
                        return 'تشرين الأول';
                    }
                }
            }
            // إذا كانت قيمة الدورة قيمة نصية واحدة
            else if (is_string($cycles)) {
                if (stripos($cycles, 'نيسان') !== false) {
                    return 'نيسان';
                }
                if (stripos($cycles, 'تشرين') !== false) {
                    return 'تشرين الأول';
                }
            }
        }



        return null;
    }


public function hasNoApprovedProgram(string $specializationName): bool
{ 
    $specializationID = Specialization::where('name', $specializationName)->value('id');
    if (!$specializationID) {
        return true;
    }
    $hasExam = Exam::where('specialization_id', $specializationID)->value('program_id');
    $hasProgram=Program::where('id',$hasExam)->where('approved',ExamRequestEnum::APPROVED->value)->exists();
    return !$hasProgram;
}

}
