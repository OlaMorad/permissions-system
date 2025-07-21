<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\ExamQuestionShowService;

class ExamController extends Controller
{
    public function __construct(protected ExamQuestionShowService $exam) {}
    public function show_exam_quetions()
    {
        $doctor = Auth::user()->doctor->id;

        return $this->exam->getTodayExamQuestionsForSpecialization($doctor);
    }
}
