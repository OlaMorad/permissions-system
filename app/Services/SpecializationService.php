<?php

namespace App\Services;

use App\Http\Resources\successResource;
use App\Models\Specialization;

class SpecializationService
{

    public function store(array $data)
    {
        $data=Specialization::create($data);
        return (new successResource($data))->response()->setStatusCode(201);
    }


}
