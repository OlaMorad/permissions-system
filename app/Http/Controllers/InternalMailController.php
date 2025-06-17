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

        //انشاء البريد الداخلي من قب الموظف او الادمن او السب ادمن
      $response=  $this->InternalMailService->create_internal_mail($request);
      return new successResource([]);
    }


//عرض البريد الصادر حسب  الحالة
    public function show_internal_mails_by_status($status){
        return  $response=  $this->InternalMailService->show_internal_mails_by_status($status);
       return new successResource([$response]);
    }

//تعديل حالة البريد من قبل المدير
    public function edit_status_internal_mails(StatusRequest $request){

   return $response=  $this->InternalMailService->edit_status_internal_mails($request);
}


//عرض البريد الداخلي الوارد للمديراو الادمن او السب ادمن
public function show_import_internal_mails(){
     return $response=  $this->InternalMailService->show_import_internal_mails();
}

//عرض تفاصيل البريد الصادر
public function show_export_internal_mail_details(IdInternalMailRequest $id){

return $this->InternalMailService->show_export_internal_mail_details($id);

}

public function show_import_internal_mail_details(IdInternalMailRequest $id){

return $this->InternalMailService->show_import_internal_mail_details($id);

}


}
