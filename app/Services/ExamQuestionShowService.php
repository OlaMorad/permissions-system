<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Exam;
use App\Models\Specialization;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class ExamQuestionShowService
{

    public function getTodayExamQuestionsForSpecialization(int $doctorId)
    {
        $now = Carbon::now();

        // : إيجاد الامتحان المتاح حالياً حسب التاريخ والوقت
        $exam = Exam::whereDate('date', $now->toDateString())
            ->whereTime('start_time', '<=', $now->toTimeString())
            ->whereTime('end_time', '>=', $now->toTimeString())
            ->first();

        if (!$exam) {
            return response()->json([
                'message' => 'لا يوجد امتحان متاح حاليًا.',
            ], 404);
        }


        $examRequest = Candidate::where('doctor_id', $doctorId)
            ->where('exam_id', $exam->id)
            ->whereNull('degree')
            ->first();

        if (!$examRequest) {
            return response()->json([
                'message' => 'الطبيب غير مرشح لهذا الامتحان.',
            ], 403);
        }
$specialization=Specialization::where('id',$exam->specialization_id)->first();

        // إرجاع الأسئلة بصيغة مرتبة
return response()->json([
    'exam_id' => $exam->id,
    'specialization'=>$specialization->name,
    'exam_date' => $exam->date,
    'start_time' => $exam->start_time,
    'end_time' => $exam->end_time,
    'questions' => $exam->questions->map(function ($question) {
        return [
            'id' => $question->pivot->id, // ID من جدول exam_questions
            'question' => Crypt::decryptString($question->question),
            'options' => [
                'A' => Crypt::decryptString($question->option_a),
                'B' => Crypt::decryptString($question->option_b),
                'C' => Crypt::decryptString($question->option_c),
                'D' => Crypt::decryptString($question->option_d),
            ],
        ];
    }),
]);


    }
}
