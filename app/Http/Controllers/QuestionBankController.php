<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  App\Services\QuestionService;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Resources\successResource;

class QuestionBankController extends Controller
{
       public function __construct(protected QuestionService $questionService) {}

    public function addManual(StoreQuestionRequest $request)
    {
      return  $this->questionService->addFromForm($request);
    }

    public function importFromExcel(Request $request)
    {
            $request->validate([
        'file' => 'required|file|mimes:xlsx,xls,csv',
        'specialization_id' => 'required|exists:specializations,id',
    ]);
        $this->questionService->addFromExcel( $request);
        return new successResource('تم الاستيراد من الإكسل');
    }
}
