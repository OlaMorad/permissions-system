<?php

namespace App\Services;

use App\Enums\ExamRequestEnum;
use App\Models\Specialization;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\successResource;

class SpecializationService
{

    public function store(array $data)
    {
        $data = Specialization::create($data);
        return (new successResource($data))->response()->setStatusCode(201);
    }

    public function update(int $id, array $data)
    {
        $specialization = Specialization::findOrFail($id);

        $specialization->update($data);

        return new successResource($specialization);
    }

    public function show_my_Specialization()
    {

        $doctor = Auth::user()->doctor->specializations;
        return new successResource([$doctor]);
    }

    public function filter_Specialization($bachelors_degree)
    {
        $specializations = Specialization::where('bachelors_degree', $bachelors_degree)
            ->where('status', ExamRequestEnum::APPROVED->value) // فلترة المقبول
            ->get()
            ->makeHidden('status'); // إخفاء حقل status

        return new successResource($specializations);
    }
    // الموافقة على اضافة اختصاص او تعديل اختصاص
    public function changeStatus(int $id, string $status)
    {
        $specialization = Specialization::findOrFail($id);

        $specialization->update([
            'status' => $status,
        ]);

        return new successResource([
            'id'     => $specialization->id,
            'new_status' => $specialization->status,
        ]);
    }
}
