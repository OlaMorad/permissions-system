<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmployeeService;
use App\Services\RegisterService;
use App\Http\Requests\UserRequest;
use App\Http\Resources\successResource;
use App\Http\Requests\UpdateEmployeeRequest;

class employeeController extends Controller
{
    public function __construct(protected RegisterService $registerService, protected EmployeeService $employeeService) {}


    public function create_employee(UserRequest $request)
    {
        $this->registerService->register_employee($request->validated());
        return new successResource([]);
    }

    public function edit_employee_information(UpdateEmployeeRequest $request)
    {
        $respone = $this->employeeService->edit_employee_information($request);
        return new successResource([
            'employee' => $respone
        ]);
    }

    public function show_employees()
    {
        return    $respone = $this->employeeService->show_employees();
    }
    //تفعيل او الغاء تفعيل الموظف
    // public function convert_employee_status(Request $request)
    // {
    //     $request->validate([
    //         'id' => 'required|exists:employees,id',
    //     ]);
    //     return    $respone = $this->employeeService->convert_employee_status($request['id']);
    // }
}
