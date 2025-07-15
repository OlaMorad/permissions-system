<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExamRequest;
use App\Http\Resources\successResource;
use App\Services\ExamRequestService;
use Illuminate\Http\Request;

class ExamRequestController extends Controller
{

    public function __construct(protected ExamRequestService $exam)
    {
    }
    public function create_form_content_exam(ExamRequest $request){
    $this->exam->create_form_content_exam($request);
    return new successResource([]);
    }
}
