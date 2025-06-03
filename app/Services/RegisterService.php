<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use App\Models\Manager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class RegisterService
{
    public function registerManager(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $role = Role::find($data['role_id']);
        $user->assignRole($role->name);

        Manager::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);

        return $user;
    }

    public function register_employee(array $data): User
    {
        //  المستخدم الحالي (رئيس القسم)
        $managerUser = Auth::user();

        //  معرفة الدور الحالي
        $managerRole = $managerUser->getRoleNames()->first();

        //  من خلال الدور، نحدد الدور المسموح للموظف
        $employeeRoleName = $this->allowedRolesMap[$managerRole] ?? null;

        if (!$employeeRoleName) {
            throw new \Exception('هذا المدير لا يملك صلاحية إنشاء موظفين.');
        }
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $user->assignRole($employeeRoleName);

        $manager = Manager::where('user_id', $managerUser->id)->firstOrFail();

        Employee::create([
            'user_id' => $user->id,
            'manager_id' => $manager->id,
            'role_id' => Role::where('name', $employeeRoleName)->first()->id,
        ]);

        return $user;
    }
    protected array $allowedRolesMap = [
        'Head of Front Desk' => 'Front Desk User',
        'Head of Certificate Officer' => 'Certificate Officer',
        'Head of Finance Officer' => 'Finance Officer',
        'Head of Academic Committee' => 'Academic Committee',
        'Head of Exam Officer' => 'Exam Officer',
        'Head of Residency Officer' => 'Residency Officer',
        'Head of Selection & Admission Officer' => 'Selection & Admission Officer',
    ];
}
