<?php

namespace App\Http\Controllers;

use App\Enums\FormStatus;
use App\Http\Requests\FormStatusRequest;
use App\Http\Requests\ManualFormRequest;
use App\Http\Requests\UploadWordRequest;
use App\Http\Resources\successResource;
use App\Models\Form;
use App\Services\ManualFormInputService;
use App\Services\FormCreationService;
use App\Services\FormFactoryService;
use App\Services\FormService;
use App\Services\WordFormInputService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormController extends Controller
{
    public function __construct(
        protected FormFactoryService $factory,
        protected FormService $formService
    ) {}


    public function storeFromWord(UploadWordRequest $request)
    {
        $this->factory->createFromWord($request);
        return new successResource(['جاري معالجة الملف، سيتم إنشاء النموذج قريباً']);
    }

    public function storeManually(ManualFormRequest $request)
    {
        $this->factory->createFromManual($request);
        return new successResource(['تمت إضافة المعاملة بنجاح']);
    }
    // عرض كل المعاملات بغض النظر عن الحالة و التكلفة ما تكون صفر مشان ما تنعرض طلبات الامتحان ضمنهم
    public function index()
    {
        $forms = Form::where('cost', '!=', 0)->get();

        if (Auth::user()->hasRole('المدير')) {
            $data = $forms->map(function ($form) {
                return [
                    'id' => $form->id,
                    'status' => $form->status,
                    'name' => $form->name,
                    'created_at' => $form->created_at->format('Y-m-d'),
                    'received_at' => $form->created_at->format('Y-m-d'),
                ];
            });
        } else {
            $data = $forms->makeHidden(['updated_at', 'cost']);
        }

        return new successResource([$data]);
    }

    // عرض تفاصيل الفورم يعني  عناصره
    public function show_Form($id)
    {
        $form = Form::with('elements')->findOrFail($id);
        $response = [
            'form_name'=> $form->name,
            'elements' => $form->elements
        ];
        return new successResource([$response]);
    }
    // عرض المعاملات الفعالة للطبيب و تكون تكلفتها مو صفر مشان ما تطلع طلبات الامتحان معهم
    public function activeForms()
    {
        $forms = Form::where('status', FormStatus::Active->value)
            ->where('cost', '!=', 0)->select('id', 'name', 'cost')->get();

        return new successResource($forms);
    }
    // عرض طلبات الامتحانات بتكون تكلفتها صفر
    public function requestForms()
    {
        $forms = Form::where('cost', 0)->select('id', 'name')->get();
        return new successResource($forms);
    }

    public function formReviewDecision($id, FormStatusRequest $request)
    {
        return $this->formService->changeUnderReviewStatus((int)$id, $request->status());
    }
    public function toggleStatus($id)
    {
        return $this->formService->toggleActiveStatus((int)$id);
    }
}
