<?php

namespace App\Services;

use App\Models\Path;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserRoleService
{
    // جلب path_id للمستخدم الحالي حسب دوره
    public function getUserPathId(): ?int
    {
        $user = Auth::user();
        $roleName = $user->getRoleNames()->first();
        $role = Role::where('name', $roleName)->first();

        return $role?->path_id;
    }

    // تحقق إذا المستخدم من المالية
    public function isFinancial(): bool
    {
        $roleName = Auth::user()->getRoleNames()->first();

        return in_array($roleName, ['موظف المالية', 'رئيس المالية']);
    }
    // تحقق حسب اي دي الباث اذا الباث مالية او لا
    public function isFinancialPath(int $pathId): bool
    {
        // إذا كان اسم الباث "المالية"
        $role = Path::find($pathId);
        return $role && $role->name === 'المالية';
    }


    // تحقق إذا المستخدم هو رئيس قسم
    public function isSectionHead(string $roleName): bool
    {
        return str_starts_with($roleName, 'رئيس');
    }
    // تحقق إذا المستخدم هو المدير أو نائب المدير
    public function isManager(string $roleName): bool
    {
        return in_array($roleName, ['المدير', 'نائب المدير']);
    }
    // تحقق إذا المستخدم طبيب
    public function isDoctor(string $roleName): bool
    {
        return $roleName === 'الطبيب';
    }

    // تحقق إذا المستخدم موظف عادي (ليس مدير، نائب مدير، رئيس قسم، ولا طبيب)
    public function isEmployee(): bool
    {
        $roleName = $this->getUserRoleName();

        return !$this->isManager($roleName)
            && !$this->isSectionHead($roleName)
            && !$this->isDoctor($roleName);
    }

    // اختياري: جلب اسم الدور الحالي للمستخدم
    public function getUserRoleName(): ?string
    {
        return Auth::user()->getRoleNames()->first();
    }
}
