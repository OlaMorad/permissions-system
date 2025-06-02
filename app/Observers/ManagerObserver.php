<?php

namespace App\Observers;

use App\Models\Manager;

class ManagerObserver
{
    public function created(Manager $manager)
    {
        // جلب المستخدم المرتبط بالمدير
        $user = $manager->user;

        // جلب الدور المرتبط بهذا المدير
        $role = $manager->role;

        // تعيين الدور للمستخدم
        if ($user && $role) {
            $user->assignRole($role->name);
        }
    }
}

