<?php

namespace App\Http\Controllers;

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
        $data = Specialization::all();
        return new successResource($data);
    }
    // التعديل على احد الاختصاصات
    public function update(UpdateSpecializationRequest $request, $id)
    {
        return $this->SpecializationService->update($id, $request->validated());
    }

    public function show_my_Specialization(){
          return $this->SpecializationService->show_my_Specialization();
    }
}
