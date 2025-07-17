<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\ExamRequestEnum;
use App\Http\Requests\ExamRequest;
use App\Services\ExamRequestService;
use Illuminate\Validation\Rules\Enum;
use App\Http\Resources\successResource;

class ExamRequestController extends Controller
{

    public function __construct(protected ExamRequestService $exam)
    {
    }
    public function create_form_content_exam(ExamRequest $request){
    try {
        $this->exam->create_form_content_exam($request);
        return new successResource([]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => $e->getMessage()
        ], 409); // Conflict
    }
    }

//عرض الطلب معبى بناء على رقم الفورم كونتينت
    public function show_form_content_exam(Request $request){
        $validated = $request->validate(['uuid'=>'required|exists:exam_requests,uuid']);
      return new successResource($this->exam->show_form_content_exam($validated));
    }

public function edit_form_content_exam_status(Request $request){
    $validated = $request->validate([
        'uuid'=>'required|exists:exam_requests,uuid',
        'status'=>['required', new Enum(ExamRequestEnum::class)]

    ]);
      return new successResource($this->exam->edit_form_content_exam_status($validated['uuid'],$validated['status']));
}

public function show_all_import_request_exam(){
  return  $this->exam->show_all_import_request_exam();
}


public function show_all_end_request_exam(){
  return  $this->exam->show_all_end_request_exam();
}
}
