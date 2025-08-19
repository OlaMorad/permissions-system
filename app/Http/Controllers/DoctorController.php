<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DoctorService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\successResource;

class DoctorController extends Controller
{

    public function __construct(protected DoctorService $doctor) {}
    // اضافة اختصاص للطبيب
    public function add_specialization(Request $request)
    {
        $id = $request->validate([
            'id' => 'required|exists:specializations,id'
        ]);
        return  $this->doctor->add_specialization($id);
    }

    public function show_welcome_message(){
    return new successResource(Auth::user()->name);
    }
}
