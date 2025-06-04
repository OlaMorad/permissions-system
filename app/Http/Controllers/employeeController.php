<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\successResource;
use App\Services\RegisterService;

class employeeController extends Controller
{
    public function __construct(protected RegisterService $registerService) {}


    public function create_employee(UserRequest $request)
    {
        $employee = $this->registerService->register_employee($request->validated());
      return new successResource([
        'user'=>$employee
      ]);

    }


}
