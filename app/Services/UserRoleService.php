<?php

namespace App\Services;

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

    // تحقق إذا المستخدم هو رئيس قسم
    public function isSectionHead(string $roleName): bool
    {
        return str_starts_with($roleName, 'رئيس');
    }

    // اختياري: جلب اسم الدور الحالي للمستخدم
    public function getUserRoleName(): ?string
    {
        return Auth::user()->getRoleNames()->first();
    }
}
