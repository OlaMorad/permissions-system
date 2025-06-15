<?php

namespace App\Services;

use App\Http\Resources\failResource;
use App\Http\Resources\successResource;
use App\Models\User;
use App\Models\Manager;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountsManagementService
{
    public function resetPassword(int $userId, string $oldPass, string $newPass)
    {
        $current = Auth::user();
        $target = User::findOrFail($userId);


        //  التحقق من كلمة المرور القديمة
        if (!$this->checkOldPassword($target, $oldPass)) {
            return new failResource(["كلمة المرور القديمة غير صحيحة"]);
        }


        // فحص صلاحية تغيير كلمة المرور
        if (
            $this->adminToSubAdmin($current, $target)
            || $this->subAdminToManager($current, $target)
            || $this->managerToEmployee($current, $target)
        ) {

            $this->changePassword($target, $newPass);
            return new successResource(["تم تغيير كلمة المرور بنجاح"]);
        }

        return new failResource(["ليس لديك صلاحية تغيير كلمة المرور لهذا المستخدم."]);
    }


    // التحقق من الباسورد القديمة
    private function checkOldPassword(User $user, string $oldPass): bool
    {
        return Hash::check($oldPass, $user->password);
    }


    // المدير يغير كلمة المرور لنائبه
    private function adminToSubAdmin($current, $target): bool
    {
        return $current->hasRole('admin') && $target->hasRole('sub_admin');
    }



    // نائب المدير يغير كلمة المرور لروؤساء الأقسام
    private function subAdminToManager($current, $target): bool
    {
        return $current->hasRole('sub_admin') &&
            Manager::where('user_id', $target->id)->exists();
    }



    //  رئيس كل قسم يغير كلمة المرور للموظفين الذين يعملون في قسمه
    private function managerToEmployee($current, $target): bool
    {
        $manager = Manager::where('user_id', $current->id)->first();
        if (!$manager) return false;

        $role = $current->getRoleNames()->first();
        if (!str_starts_with($role, 'Head of')) return false;

        return Employee::where('user_id', $target->id)
            ->where('manager_id', $manager->id)
            ->exists();
    }



    // تغيير كلمة المرور
    private function changePassword(User $user, string $newPass): void
    {
        $user->password = Hash::make($newPass);
        $user->save();
    }
}
