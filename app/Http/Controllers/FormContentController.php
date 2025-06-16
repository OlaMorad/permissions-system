<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FormContentService;
use App\Http\Requests\ContentFormRequest;

class FormContentController extends Controller
{
        public function __construct(protected FormContentService $FormContentService) {}

    public function create_form_content(ContentFormRequest $request){
$validated = $request->validated();
   $formContent= $this->FormContentService->create_form_content($validated);
    return response()->json([
        'message' => 'تم إنشاء المعاملة بنجاح',
        'data' => $formContent,
    ]);
    }
}
