<?php

namespace App\Presenters;

use App\Models\Program;
use Illuminate\Support\Collection;

class ProgramPresenter
{
    public static function program(Collection $programs): array
    {
        return $programs->map(function (Program $program) {
            return [
                'الشهر' => $program->month,
                'السنة' => $program->year,
                'الحالة' => $program->status?->value,
                'الموافقة' => $program->approved->value,
                'عدد المواد' => $program->exams_count,
                'تاريخ البدء' => $program->start_date,
                'تاريخ الانتهاء' => $program->end_date,
                'تاريخ الإرسال' => $program->created_at->format('Y-m-d H:i'),
            ];
        })->toArray();
    }

    public static function exams(Collection $exams): array
    {
        return $exams->map(function ($exam) {
            return [
                'الاختصاص' => $exam->specialization->name,
                'اليوم' => $exam->day,
                'التاريخ' => $exam->date,
                'الساعة' => $exam->exam_time,
                'الحالة' => $exam->status,
                'بسيط' => $exam->simple_ratio . '%',
                'متوسط' => $exam->average_ratio . '%',
                'صعب' => $exam->hard_ratio . '%',
            ];
        })->toArray();
    }
}
