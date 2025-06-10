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
    public function resetPassword(int $UserId, string $oldPassword, string $newPassword)
    {
        $currentUser = Auth::user();
        $targetUser = User::findOrFail($UserId);

        // تحقق أن كلمة المرور القديمة صحيحة
        if (!Hash::check($oldPassword, $targetUser->password)) {
            return new failResource(["كلمة المرور القديمة غير صحيحة"]);
        }

        // sub_admin يمكنه تغيير كلمة مرور أي مانجر
        if ($currentUser->hasRole('sub_admin')) {
            $isManager = Manager::where('user_id', $targetUser->id)->exists();
            if ($isManager) {
                $targetUser->password = Hash::make($newPassword);
                if ($targetUser->save()) {
                    return new successResource(["تم تغيير كلمة المرور بنجاح"]);
                }
            }
        }

        // المدير يقدر يغير لموظف تحت إشرافه
        if ($currentUser->getRoleNames()->first() && str_starts_with($currentUser->getRoleNames()->first(), 'Head of')) {
            $manager = Manager::where('user_id', $currentUser->id)->first();
            if (!$manager) return false;

            $isEmployee = Employee::where('user_id', $targetUser->id)
                ->where('manager_id', $manager->id)
                ->exists();

            if ($isEmployee) {
                $targetUser->password = Hash::make($newPassword);
                if ($targetUser->save()) {
                    return new successResource(["تم تغيير كلمة المرور بنجاح"]);
                }
            }
        }

        return new failResource(["ليس لديك صلاحية تغيير كلمة المرور لهذاالمستخدم"]);
    }
}
