<?php

namespace App\Services;

use App\Http\Resources\successResource;
use App\Models\Employee;
use App\Models\User;
use App\Models\Manager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class RegisterService
{
    public function registerManager(array $data)
    {
        $user = User::create_user($data);

        $role = Role::find($data['role_id']);

        Manager::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);
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

        $user = User::create_user($data);

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
