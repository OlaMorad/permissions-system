<?php

namespace App\Http\Controllers;

use App\Http\Requests\IdInternalMailRequest;
use App\Http\Requests\InternalMailRequest;
use App\Http\Requests\StatusRequest;
use App\Http\Resources\successResource;
use App\Services\InternalMailService;
use Illuminate\Http\Request;

class InternalMailController extends Controller
{

        public function __construct(protected InternalMailService $InternalMailService) {}

    public function create_internal_mail(InternalMailRequest $request){

        //انشاء البريد الداخلي من قبل الموظف او الادمن او السب ادمن
    return  $response=  $this->InternalMailService->create_internal_mail($request);
    }

//عرض البريد الصادر للموظف و المدير
    public function show_internal_mails_export(){
          $response=  $this->InternalMailService->show_internal_mails_export();
       return new successResource([$response]);
    }

//تعديل حالة البريد من قبل المدير
    public function edit_status_internal_mails(StatusRequest $request){

   return $response=  $this->InternalMailService->edit_status_internal_mails($request);
}


//عرض البريد الداخلي الوارد للمديراو الادمن او السب ادمن
public function show_import_internal_mails(){
      $response=  $this->InternalMailService->show_import_internal_mails();
      return new successResource([$response]);
}


public function show_internal_mail_details(IdInternalMailRequest $request){

return $this->InternalMailService->show_internal_mail_details($request->uuid);

}

}
