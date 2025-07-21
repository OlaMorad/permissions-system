<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnswerExamRequest;
use App\Services\AnswerExamService;
use Illuminate\Support\Facades\Auth;
use App\Services\ExamQuestionShowService;

class ExamController extends Controller
{
    public function __construct(
        protected ExamQuestionShowService $exam,
        protected AnswerExamService $answers
    ) {}
    public function show_exam_quetions()
    {
        $doctor = Auth::user()->doctor->id;

        return $this->exam->getTodayExamQuestionsForSpecialization($doctor);
    }

    public function submit_answers(AnswerExamRequest $request)
    {
        $data = $request->validated();

        return $this->answers->submitDoctorAnswers($data);
    }
}
