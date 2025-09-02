<?php

namespace App\Http\Controllers;

use App\Enums\ExamRequestEnum;
use App\Http\Requests\AddSpecializationRequest;
use App\Http\Requests\UpdateSpecializationRequest;
use App\Http\Resources\successResource;
use App\Models\Specialization;
use App\Services\SpecializationService;
use Illuminate\Http\Request;

class SpecializationController extends Controller
{
    public function __construct(
        protected SpecializationService $SpecializationService,
    ) {}
    // اضافة اختصاص جديد
    public function store(AddSpecializationRequest $request)
    {
        return $this->SpecializationService->store($request->validated());
    }
    // عرض كل الاختصاصات
    public function index()
    {
        $data = Specialization::where('status', ExamRequestEnum::APPROVED->value)->get();
        // إخفاء حقل status من الريسبونس
        $data->makeHidden('status');
        return new SuccessResource($data);
    }
    // التعديل على احد الاختصاصات
    public function update(UpdateSpecializationRequest $request, $id)
    {
        $specialization = $this->SpecializationService->update($id, $request->validated());
        // إخفاء حقل status قبل الإرجاع
        $specialization->makeHidden('status');

        return $specialization;
    }

    public function show_my_Specialization()
    {
        return $this->SpecializationService->show_my_Specialization();
    }

    public function filter_Specialization($bachelors_degree)
    {
        return $this->SpecializationService->filter_Specialization($bachelors_degree);
    }
}
