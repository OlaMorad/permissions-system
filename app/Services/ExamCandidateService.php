<?php

namespace App\Services;

use App\Http\Resources\successResource;
use App\Models\Candidate;
use App\Models\Exam;
use App\Presenters\ProgramPresenter;
use Carbon\Carbon;

class ExamCandidateService
{
    // عرض المرشجين لامتحان معين
    public function getCandidatesByExamId(int $examId)
    {
        $exam = Exam::find($examId);

        if (!$exam) {
            return response()->json([
                'message' => 'الامتحان غير موجود.'
            ], 404);
        }

        $today = Carbon::today();
        $date = Carbon::parse($exam->date);

        $diffInDays = $today->diffInDays($date, false); // false ليحسب الأيام بالسالب لو التاريخ قبل اليوم

        if ($diffInDays > 7) {
            // إذا بقي أكثر من أسبوع على الامتحان
            return new successResource("لم تنتهِ فترة طلبات الترشيح بعد.");
        }

        // إذا باقي أسبوع أو أقل
        $candidates = $exam->candidates()
            ->with('doctor.user')->get();

        return new successResource(ProgramPresenter::candidates($candidates));
    }
    // عرض المتقدمين لامتحان معين
    public function get_present_candidates_ByExamId(int $examId)
    {
        $exam = Exam::find($examId);

        if (!$exam) {
            return response()->json([
                'message' => 'الامتحان غير موجود.'
            ], 404);
        }

        $today = Carbon::today();
        $examDate = Carbon::parse($exam->date);

        if (!$examDate->isSameDay($today)) {
            return new successResource('لم يحن موعد الامتحان بعد');
        }

        $candidates = $exam->candidates()
            ->whereNotNull('degree') // فقط اللي معهم علامة
            ->with('doctor.user')
            ->get();

        return new successResource(ProgramPresenter::Candidates_present($candidates));
    }
    // عرض كل علامات الاطباء
    public function get_all_present_candidates()
    {
        $candidates = Candidate::whereNotNull('degree')
            ->with(['doctor.user', 'exam.specialization'])
            ->get();

        return new successResource(ProgramPresenter::Doctor_Degree($candidates));
    }
}
