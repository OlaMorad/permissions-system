<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManualFormRequest;
use App\Http\Requests\UploadWordRequest;
use App\Http\Resources\successResource;
use App\Models\Form;
use App\Services\ManualFormInputService;
use App\Services\FormCreationService;
use App\Services\FormService;
use App\Services\WordFormInputService;
use Illuminate\Http\Request;

class FormController extends Controller
{
    protected FormCreationService $formService;

    public function __construct(FormCreationService $formService)
    {
        $this->formService = $formService;
    }

    //  إنشاء فورم من ملف Word
    public function storeFromWord(UploadWordRequest $request)
    {
        $file = $request->file('file');
        $path = $file->getRealPath();
        $pathIds = $request->input('path_ids');
        $extractor = new WordFormInputService($path);
        $form = $this->formService->create_Form($extractor, $file->getClientOriginalName(), $pathIds);

        return new successResource([$form]);
    }

    //  إنشاء فورم من إدخال يدوي
    public function storeManually(ManualFormRequest $request)
    {
        $data = $request->validated();
        $manual = new ManualFormInputService($data['elements']);
        $form = $this->formService->create_Form($manual, $data['name'], $data['path_ids']);

        return new successResource([$form]);
    }

    public function index()
    {
        $data = Form::all();
        return new successResource([$data]);
    }

    public function show_Form($id)
    {
        $form = Form::with('elements','paths')->findOrFail($id);
        return new successResource([$form]);
    }

    public function show_active_Form()
    {
        $form = Form::where('status','active')->select('name')->get();
        return new successResource([$form]);
    }


    public function UpdateFormStatus($id, FormService $formService)
    {
        return $formService->UpdateStatus((int)$id);
    }
}
