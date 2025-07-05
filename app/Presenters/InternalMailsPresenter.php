<?php

namespace App\Presenters;

use App\Models\Path;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InternalMailsPresenter{


    public  array $headRoles = [
        'المدير',
        'نائب المدير',
        'رئيس الديوان',
        'رئيس المالية',
        'رئيس مجالس علمية',
        'رئيس الشهادات',
        'رئيس الامتحانات',
        'رئيس الإقامة',
        'رئيس المفاضلة',
    ];


    //مشان لما يكون يلي مكريت البريد هو الموظف نبدل اسمه لرئيس بالارجاع
    public static function sender_mail($roleName)
    {
        if (str_starts_with($roleName, 'موظف ')) {
            return preg_replace('/^موظف /u', 'رئيس ', $roleName);
        }
        return $roleName;
    }



    //التحقق اذا كان المستخدم الحالي ادمن او سب ادمن
    public function is_admin()
    {

        $currentUser = Auth::user();
        $userRole = $currentUser->getRoleNames()->first();
        $adminRoles = ['المدير', 'نائب المدير'];
        if (in_array($userRole, $adminRoles))
            return true;
    }



    public  function getUserPathId($userRole)
    {
        if (is_string($userRole)) {
            $role = DB::table('roles')->where('name', $userRole)->first();
        } else {
            $role = $userRole;
        }

        return $role?->path_id ?? null;
    }


      public function get_path_name()
    {
        $currentUser = Auth::user();
        $roleName = $currentUser->getRoleNames()->first();
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $pathId = $role->path_id;
            $pathName = Path::where('id', $pathId)->first();

            return [
                'path_name' => $pathName,
                'path_id' => $pathId
            ];
        }
        return null;
    }

      // دالة مساعدة لجلب المدير الحالي مع دوره
    public  function getCurrentManagerWithRole()
    {
        $currentUser = Auth::user();
        $manager = DB::table('managers')->where('user_id', $currentUser->id)->first();
        if (!$manager) return null;

        $role = DB::table('roles')->where('id', $manager->role_id)->first();
        return ['manager' => $manager, 'role' => $role];
    }
}
