<?php

namespace App\Services;

use App\Http\Resources\successResource;
use App\Models\Employee;
use App\Models\User;
use App\Models\Manager;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class RegisterService
{
    public function register_employee(array $data): User
    {
        $user = User::create_user($data);

        $role = Role::findOrFail($data['role_id']);
        $user->assignRole($role->name);

        if (str_starts_with($role->name, 'رئيس')) {
            Manager::create([
                'user_id' => $user->id,
                'role_id' => $role->id,
            ]);
        } else {
            // جلب الـ path_id من الدور المُعطى
            $targetPathId = $role->path_id;

            // جلب المدير الذي ينتمي لنفس الباث
            $manager = Manager::whereHas('role', function ($query) use ($targetPathId) {
                $query->where('path_id', $targetPathId);
            })->first();

            if (!$manager) {
                throw new Exception('لا يوجد رئيس لهذه الدائرة');
            }

            Employee::create([
                'user_id'    => $user->id,
                'manager_id' => $manager->id,
                'role_id'    => $role->id,
            ]);
        }

        return $user;
    }
}
