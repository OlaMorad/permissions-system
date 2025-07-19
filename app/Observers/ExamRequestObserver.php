<?php

namespace App\Observers;

use App\Models\ExamRequest;
use App\Models\Candidate;
use App\Models\Exam;
use App\Enums\ExamRequestEnum;
use App\Models\Specialization;

class ExamRequestObserver
{
    /**
     * Handle the ExamRequest "created" event.
     */
    public function created(ExamRequest $examRequest): void
    {
        //
    }

    /**
     * Handle the ExamRequest "updated" event.
     */
    public function updated(ExamRequest $examRequest): void
    {
        // تحقق من أن الحالة تغيرت إلى مقبول
        if (
            $examRequest->wasChanged('status') &&
            $examRequest->status === ExamRequestEnum::APPROVED
        ) {
            $formName = $examRequest->formContent?->form?->name;

            if (
                in_array($formName, [
                    'طلب ترشيح لامتحان داخل القطر',
                    'طلب ترشيح للأمتحان خارج القطر'
                ])
            ) {
                $specialtyElement = $examRequest->formContent->elementValues->first(function ($elementValue) {
                    $label = $elementValue->formElement->label ?? '';
                    return stripos($label, 'اختصاص') !== false;
                });

                $specialtyName = $specialtyElement?->value;

                $exam = null;

                if ($specialtyName) {
                    $specialization = Specialization::where('name', 'like', "%$specialtyName%")->first();

                    if ($specialization) {
                        $exam = Exam::where('specialization_id', $specialization->id)
                            ->latest('date')
                            ->first();
                    }
                }
                if ($exam) {
                    // توليد رقم امتحان مكون من 6 خانات عشوائية غير مكررة
                    do {
                        $examNumber = random_int(100000, 999999);
                    } while (Candidate::where('exam_number', $examNumber)->exists());

                    Candidate::create([
                        'exam_id' => $exam->id,
                        'doctor_id' => $examRequest->doctor_id,
                        'exam_number' => $examNumber,
                        'exam_date' => $exam->date,
                        'nomination_date' => now(),
                    ]);
                }
            }
        }}

    /**
     * Handle the ExamRequest "deleted" event.
     */
    public function deleted(ExamRequest $examRequest): void
    {
        //
    }

    /**
     * Handle the ExamRequest "restored" event.
     */
    public function restored(ExamRequest $examRequest): void
    {
        //
    }

    /**
     * Handle the ExamRequest "force deleted" event.
     */
    public function forceDeleted(ExamRequest $examRequest): void
    {
        //
    }
}
