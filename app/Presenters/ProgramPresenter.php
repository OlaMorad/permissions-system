<?php

namespace App\Presenters;

use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProgramPresenter
{
    public static function program(Collection $programs): array
    {
        return $programs->map(function (Program $program) {
            return [
                'id' => $program->id,
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
    public static function simpleProgram(Collection $programs): array
    {
        return $programs->map(function (Program $program) {
            return [
                'id' => $program->id,
                'الشهر' => $program->month,
                'السنة' => $program->year,
                'الحالة' => $program->status?->value,
                'تاريخ البدء' => $program->start_date,
                'تاريخ الانتهاء' => $program->end_date,
            ];
        })->toArray();
    }

    public static function exams(Collection $exams): array
    {
        return $exams->map(function ($exam) {
            return [
                'id' => $exam->id,
                'الاختصاص' => $exam->specialization->name,
                'اليوم' => $exam->day,
                'التاريخ' => $exam->date,
                'الساعة' => $exam->exam_time,
                'الحالة' => $exam->status,
                'عدد المرشحين' => $exam->candidates_count ?? null,
                'عدد المتقدمين' => $exam->present_candidates_count ?? null,
                'نسبة النجاح' => $exam->success_rate !== null ? $exam->success_rate . '%' : null,
                'بسيط' => $exam->simple_ratio . '%',
                'متوسط' => $exam->average_ratio . '%',
                'صعب' => $exam->hard_ratio . '%',
            ];
        })->toArray();
    }
    public static function examsForDoctor(Collection $exams): array
    {
        return $exams->map(function ($exam) {
            return [
                'id' => $exam->id,
                'الاختصاص' => $exam->specialization->name,
                'اليوم' => $exam->day,
                'التاريخ' => $exam->date,
                'الساعة' => $exam->exam_time,
                'الحالة' => $exam->status,
            ];
        })->toArray();
    }

    public static function candidates(Collection $candidates): array
    {
        return $candidates->map(function ($candidate) {
            return [
                'الرقم الامتحاني' => $candidate->exam_number,
                'صورة الطبيب' => isset($candidate->doctor->user->avatar) ? asset('storage/' . $candidate->doctor->user->avatar) : null,
                'اسم الطبيب' => $candidate->doctor->user->name ?? null,
                'تاريخ الترشيح' => $candidate->nomination_date,
            ];
        })->toArray();
    }
    public static function Candidates_present(Collection $candidates): array
    {
        return $candidates->map(function ($candidate) {
            return [
                'الرقم الامتحاني' => $candidate->exam_number,
                'اسم الطبيب' => $candidate->doctor->user->name ?? null,
                'صورة الطبيب' => isset($candidate->doctor->user->avatar) ? asset('storage/' . $candidate->doctor->user->avatar) : null,
                'الحالة' => $candidate->status,
                'العلامة' => $candidate->degree,
                'التقدير' => $candidate->rating,
                'تاريخ التقديم' => $candidate->exam_date ? Carbon::parse($candidate->exam_date)->format('Y-m-d H:i') : null,
            ];
        })->toArray();
    }
    public static function Doctor_Degree(Collection $candidates): array
    {
        return $candidates->map(function ($candidate) {
            return [
                'الرقم الامتحاني' => $candidate->exam_number,
                'اسم الطبيب' => $candidate->doctor->user->name ?? null,
                'صورة الطبيب' => isset($candidate->doctor->user->avatar) ? asset('storage/' . $candidate->doctor->user->avatar) : null,
                'الاختصاص' => $candidate->exam->specialization->name ?? null,
                'الحالة' => $candidate->status,
                'العلامة' => $candidate->degree,
                'التقدير' => $candidate->rating,
                'تاريخ التقديم' => $candidate->exam_date ? Carbon::parse($candidate->exam_date)->format('Y-m-d H:i') : null,
            ];
        })->toArray();
    }
}
