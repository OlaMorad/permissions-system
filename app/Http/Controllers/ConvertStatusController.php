<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConvertStatusRequest;
use App\Services\ConvertStatusService;
use Illuminate\Http\Request;

class ConvertStatusController extends Controller
{
    public function __construct(protected ConvertStatusService $service){}

    public function Convert_status_for_user(ConvertStatusRequest $request,$type){
    return $this->service->Convert_status_for_user($request->user_id,$type);
    }
}
