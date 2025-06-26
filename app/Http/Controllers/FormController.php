<?php

namespace App\Http\Controllers;

use App\Enums\FormStatus;
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

class FormController extends Controller
{
    public function __construct(
        protected FormFactoryService $factory,
        protected FormService $formService
    ) {}


    public function storeFromWord(UploadWordRequest $request)
    {
        $this->factory->createFromWord($request);
        return new successResource(['تمت إضافة المعاملة بنجاح']);
    }

    public function storeManually(ManualFormRequest $request)
    {
        $this->factory->createFromManual($request);
        return new successResource(['تمت إضافة المعاملة بنجاح']);
    }

    public function index()
    {
        $data = Form::all()->makeHidden(['updated_at']);
        return new successResource([$data]);
    }

    public function show_Form($id)
    {
        $form = Form::with('elements', 'paths')->findOrFail($id);
        $response = [
            'paths' => $form->paths->pluck('name'),
            'elements' => $form->elements
        ];
        return new successResource([$response]);
    }
    public function activeForms()
    {
        $forms = Form::where('status', FormStatus::Active->value)->select('id','name','cost')->get();
        return new successResource([$forms]);
    }

    public function underReviewForms()
    {
        $forms = Form::where('status', FormStatus::UNDER_REVIEW->value)->get()->makeHidden(['updated_at']);
        return new successResource([$forms]);
    }


    public function setUnderReviewToActive($id)
    {
        return $this->formService->changeUnderReviewToActive((int)$id);
    }

    public function setActiveToInactive($id)
    {
        return $this->formService->changeActiveToInactive((int)$id);
    }

    public function setInactiveToActive($id)
    {
        return $this->formService->changeInactiveToActive((int)$id);
    }
}
