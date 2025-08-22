<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AnswerExamService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AnswerExamRequest;
use App\Http\Requests\ExamNameRequest;
use App\Services\DoctorService;
use App\Services\ExamQuestionShowService;

class ExamController extends Controller
{
    public function __construct(
        protected ExamQuestionShowService $exam,
        protected AnswerExamService $answers,
        protected DoctorService $doctorService

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
    // عرض بروفايل الامتحان
    public function exam_profile()
    {
        return $this->doctorService->exam_profile();
    }
    // عرض الوقت المتبقي و تاريخ الامتحان
    public function check_exam_time()
    {
        return $this->doctorService->check_exam_time();
    }
}
