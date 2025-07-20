<?php

namespace App\Http\Controllers;

use App\Services\ExamCandidateService;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    public function __construct(
        protected ExamCandidateService $candidateService,
    ) {}
    // عرض عدد المرشحين لامتحان معين
    public function show_candidates_By_ExamId($examId)
    {
        return $this->candidateService->getCandidatesByExamId($examId);
    }
    // عرض عدد المتقدمين لامتحان معين
    public function show_present_candidates_By_ExamId($examId)
    {
        return $this->candidateService->get_present_candidates_ByExamId($examId);
    }
    // عرض كل علامات الأطباء
    public function show_all_present_candidates()
    {
        return $this->candidateService->get_all_present_candidates();
    }
}
