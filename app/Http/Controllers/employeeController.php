<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Requests\EmployeeRequest;
use App\Http\Requests\UserRequest;
use App\Models\Employee;

class employeeController extends Controller
{
     public function create_employee(UserRequest $request, $roleName)
    {
        $user = User::create_user($request);
        $role = Role::where('name', $roleName)->firstOrFail();
    
        Employee::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'manager_id'=>1
        ]);

        return response()->json(['message' => 'تم إنشاء الموظف بنجاح']);
    }


}
