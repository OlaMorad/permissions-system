<?php

namespace App\Observers;

use App\Models\Employee;

class employeeObserver
{
        public function created(Employee $employee)
    {
        // جلب المستخدم المرتبط بالموظف
        $user = $employee->user;

        // جلب الدور المرتبط بهذا الموظف
        $role = $employee->role;

        // تعيين الدور للمستخدم
        if ($user && $role) {
            $user->assignRole($role->name);
        }
    }
}
