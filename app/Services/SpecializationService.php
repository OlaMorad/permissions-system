<?php

namespace App\Services;

use App\Models\Specialization;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\successResource;

class SpecializationService
{

    public function store(array $data)
    {
        $data=Specialization::create($data);
        return (new successResource($data))->response()->setStatusCode(201);
    }

    public function update(int $id, array $data)
    {
        $specialization = Specialization::findOrFail($id);

        $specialization->update($data);

        return new successResource($specialization);
    }

    public function show_my_Specialization(){

        $doctor=Auth::user()->doctor->specializations;
        return new successResource([ $doctor]);
    }
}
