<?php

namespace App\Http\Controllers;

use App\Http\Resources\successResource;
use App\Services\DoctorService;
use Illuminate\Http\Request;

class DoctorController extends Controller
{

    public function __construct(protected DoctorService $doctor)
    {
    }



   public function add_specialization(Request $request){
 $id= $request->validate([
    'id'=>'required|exists:specializations,id'
  ]);
 return  $this->doctor->add_specialization($id);
   }
}
