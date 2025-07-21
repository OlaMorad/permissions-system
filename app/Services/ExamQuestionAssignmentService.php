<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Exam;
use App\Models\Program;
use App\Models\QuestionBank;
use Illuminate\Support\Facades\DB;

class ExamQuestionAssignmentService
{
    public function assignQuestionsToExams(Program $program): void
    {
        $avoidRecentlyUsedBefore = Carbon::now()->subMonths(12); //
        foreach ($program->exams as $exam) {
            $specializationId = $exam->specialization_id;

            //  (تقديريًا مثلاً 20 سؤال لكل امتحان)
            $totalQuestions = 20;

            $easyCount = round($exam->simple_ratio * $totalQuestions / 100);
            $mediumCount = round($exam->average_ratio * $totalQuestions / 100);
            $hardCount = round($exam->hard_ratio * $totalQuestions / 100);

            $questions = collect();

            $questions = $questions->merge(
                QuestionBank::where('specialization_id', $specializationId)
                    ->where('difficulty_level', 'بسيط')
                    ->where(function ($q) use ($avoidRecentlyUsedBefore) {
                        $q->whereNull('last_used_at')
                            ->orWhere('last_used_at', '<', $avoidRecentlyUsedBefore);
                    })
                    ->inRandomOrder()
                    ->limit($easyCount)
                    ->get()
            );

            $questions = $questions->merge(
                QuestionBank::where('specialization_id', $specializationId)
                    ->where('difficulty_level', 'متوسط')
                    ->where(function ($q) use ($avoidRecentlyUsedBefore) {
                        $q->whereNull('last_used_at')
                            ->orWhere('last_used_at', '<', $avoidRecentlyUsedBefore);
                    })
                    ->inRandomOrder()
                    ->limit($mediumCount)
                    ->get()
            );

            $questions = $questions->merge(
                QuestionBank::where('specialization_id', $specializationId)
                    ->where('difficulty_level', 'صعب')
                    ->where(function ($q) use ($avoidRecentlyUsedBefore) {
                        $q->whereNull('last_used_at')
                            ->orWhere('last_used_at', '<', $avoidRecentlyUsedBefore);
                    })
                    ->inRandomOrder()
                    ->limit($hardCount)
                    ->get()
            );

            foreach ($questions as $question) {
                DB::table('exam_questions')->insert([
                    'exam_id' => $exam->id,
                    'question_bank_id' => $question->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                    $question->update(['last_used_at' => now()]);

            }
        }
    }
}
