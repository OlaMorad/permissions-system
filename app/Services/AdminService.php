<?php

namespace App\Services;

use App\Models\Path;
use App\Models\Role;
use App\Models\Manager;
use App\Models\Employee;
use App\Services\EmployeeService;
use App\Http\Resources\successResource;

class AdminService
{
          public function __construct(protected EmployeeService $employeeService)
    {
    }
    public function show_employees_by_path($path)
    {


        $pathNAme = Path::where('name', $path)->first();
        if (!$path) {
            return response()->json(['message' => 'المسار غير موجود'], 404);
        }
        $role = Role::where('path_id', $pathNAme->id)->pluck('id');
        $employees = Employee::whereIn('role_id', $role)->with('user','role')->get();
        $managers=Manager::whereIn('role_id', $role)->with('user','role')->get();
         $stats = $this->employeeService->employeeStatistics();
        //استخدمت ماب لان عندي اكتر من موظف وكل موظف بدي اوصل لخصائصه بجدول المستخدمين
        $employeeData = $employees->map(function ($employee) use ($pathNAme, $stats) {
              $userId = $employee->id;
            return [
                'avatar' => $employee->user?->avatar ? asset('storage/' . $employee->user->avatar) : null,
                'name' => $employee->user?->name ?: null,
                'phone' => $employee->user?->phone ?: null,
                'email' => $employee->user?->email ?: null,
                'address' => $employee->user?->address ?: null,
                'office' => $pathNAme->name,
                'role'=>$employee->role?->name,
                'handled_transactions' => $stats[$userId]['handled_transactions'] ?? 0,
                'status' => $employee->user?->is_active ?: 0,
                'date join' => $employee->created_at ?: null
            ];
        });

            $managerData = $managers->map(function ($manager) use ($pathNAme) {
        return [
            'avatar' => $manager->user?->avatar ? asset('storage/' . $manager->user->avatar) : null,
            'name' => $manager->user?->name ?: null,
            'phone' => $manager->user?->phone ?: null,
             'email' => $manager->user?->email ?: null,
            'address' => $manager->user?->address ?: null,
            'office' => $pathNAme->name,
            'role' => $manager->role?->name,
            'handled_transactions' => null, // المعاملات نل
            'status' => $manager->user?->is_active ?: 0,
            'date join' => $manager->created_at ?: null
        ];
    });
     $allData = $employeeData->merge($managerData);

        return new successResource([$allData]);
    }
    public function show_employees_and_manager(){
        $managers=Manager::with('user')->get();
        $employees=Employee::with('user')->get();
       $stats = $this->employeeService->employeeStatistics();

           $employeeData = $employees->map(function ($employee)use($stats) {
              $userId = $employee->id;
            return [
                'avatar' => $employee->user?->avatar ? asset('storage/' . $employee->user->avatar) : null,
                'name' => $employee->user?->name ?: null,
                'role' => $employee->role?->name,
                'handled_transactions' => $stats[$userId]['handled_transactions'] ?? 0,
            ];
        });

            $managerData = $managers->map(function ($manager)  {
        return [
            'avatar' => $manager->user?->avatar ? asset('storage/' . $manager->user->avatar) : null,
            'name' => $manager->user?->name ?: null,
            'role' => $manager->role?->name,
            'handled_transactions' => null, // المعاملات نل
        ];
    });
   return  $employeeData->merge($managerData);

    }
}
