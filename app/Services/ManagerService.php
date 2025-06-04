<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ManagerService
{

    public function show_my_employees()
    {
        $manager = Auth::user();

        $employeesId = Employee::where('manager_id', $manager->id)->pluck('user_id');
        return $employees = DB::table('users')->whereIn('id', $employeesId)->select('name', 'email')->get();
    }
}
