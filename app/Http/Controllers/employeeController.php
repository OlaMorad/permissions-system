<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Requests\EmployeeRequest;
use App\Http\Requests\UserRequest;
use App\Models\Employee;
use App\Services\RegisterService;

class employeeController extends Controller
{
    public function __construct(protected RegisterService $registerService) {}
    public function create_employee(UserRequest $request)
    {
        $employee = $this->registerService->register_employee($request->validated());

        return response()->json(['message' => 'تم إنشاء الموظف بنجاح']);
    }


}
