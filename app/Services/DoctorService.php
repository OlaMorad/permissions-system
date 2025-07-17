<?php

namespace App\Services;

use App\Http\Resources\successResource;
use App\Models\Doctor;
use App\Models\Specialization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DoctorService
{

    public function add_specialization($id)
    {

        $doctor = Auth::user()->doctor;
       $alreadyExists = $doctor->specializations()->where('specialization_id', $id)->exists();

    if ($alreadyExists) {
        return response()->json(['message' => 'هذا التخصص مضاف مسبقًا'], 200);
    }

  $doctor->specializations()->attach($id, [
    'created_at' => now(),
    'updated_at' => now(),
]);
return new successResource([]);


    }
}
