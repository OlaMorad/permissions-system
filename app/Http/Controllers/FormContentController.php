<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FormContentService;
use App\Http\Requests\ContentFormRequest;
use App\Http\Requests\UploadReceiptRequest;
use App\Http\Resources\successResource;
use App\Services\PaymentService;

class FormContentController extends Controller
{
    public function __construct(protected FormContentService $FormContentService) {}
    // تعباية فورم معاملة
    public function create_form_content(ContentFormRequest $request)
    {
        $validated = $request->validated();
        $this->FormContentService->createFormContent($validated);
        return new successResource(['تمت تعبئة الفورم بنجاح']);
    }
    // رفع وصل
    public function uploadReceipt(UploadReceiptRequest $request, PaymentService $paymentService)
    {
        $paymentService->handlePayment(
            $request->input('uuid'),
            $request->file('receipt')
        );
        return new successResource('تم رفع إيصال الدفع و تحديث حالة الدفع ');
    }
}
