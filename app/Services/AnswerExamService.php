<?php

namespace App\Services;

use App\Enums\DegreeRating;
use App\Enums\DoctorExamStatus;
use App\Models\Candidate;
use App\Models\Exam;
use App\Models\ExamQuestion;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnswerExamService
{
    public function submitDoctorAnswers(array $data)
    {
        $doctorId = Auth::user()->doctor->id;
        $examId = $data['exam_id'];
        $answers = $data['answers'] ?? [];

        $exam = Exam::findOrFail($examId);

        // التحقق من ترشيح الطبيب لهذا الامتحان
        $candidate = Candidate::where('doctor_id', $doctorId)
            ->where('exam_id', $exam->id)
            ->whereNull('degree')
            ->first();

        if (!$candidate) {
            return response()->json([
                'message' => 'الطبيب غير مرشح لهذا الامتحان.',
            ], 403);
        }

        $correctCount = 0;
        $totalExamQuestions = DB::table('exam_questions')->where('exam_id', $examId)->count();
        $totalQuestions = count($answers);

        DB::beginTransaction();
        try {
            foreach ($answers as $answer) {
                // جلب صف exam_questions حسب id المرسل مباشرة
                $examQuestion = DB::table('exam_questions')
                    ->where('id', $answer['id'])
                    ->where('exam_id', $examId)
                    ->first();

                // جلب السؤال الأصلي من question_banks
                $question = DB::table('question_banks')->where('id', $examQuestion->question_bank_id)->first();

                // فك التشفير ومقارنة الجواب
                $correctAnswer = Crypt::decryptString($question->correct_answer);

                if ($answer['selected_option'] === $correctAnswer) {
                    $correctCount++;
                }
            }

            $degree = $totalExamQuestions > 0 ? round(($correctCount / $totalExamQuestions) * 100, 2) : 0;
            $status = DoctorExamStatus::fromDegree($degree);
            $rating = DegreeRating::fromDegree($degree);

            $candidate->update([
                'degree' => $degree,
                'status' => $status?->value,
                'rating' => $rating?->value,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'تمت معالجة الإجابات بنجاح.',
                'degree' => $degree,
                'status' => $status?->value,
                'rating' => $rating?->value,
                'answered_questions_count' => $totalQuestions,
                'total_questions_count' => $totalExamQuestions,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'حدث خطأ أثناء معالجة الإجابات.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
