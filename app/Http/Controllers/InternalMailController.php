<?php

namespace App\Http\Controllers;

use App\Http\Requests\InternalMailRequest;
use App\Http\Requests\StatusRequest;
use App\Http\Resources\successResource;
use App\Services\InternalMailService;
use Illuminate\Http\Request;

class InternalMailController extends Controller
{

        public function __construct(protected InternalMailService $InternalMailService) {}

    public function create_internal_mail(InternalMailRequest $request){

      $response=  $this->InternalMailService->create_internal_mail($request);
      return new successResource([$response]);
    }



    public function show_internal_mails_by_status($status){
        return  $response=  $this->InternalMailService->show_internal_mails_by_status($status);
       return new successResource([$response]);
    }

    public function edit_status_internal_mails(StatusRequest $request){

   return $response=  $this->InternalMailService->edit_status_internal_mails($request);
}

public function show_import_internal_mails(){
     return $response=  $this->InternalMailService->show_import_internal_mails();
}



}
