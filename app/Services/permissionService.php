<?php

namespace App\Services;

use App\Http\Resources\failResource;
use App\Http\Resources\successResource;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class permissionService
{

    public function add_permission($data, $userId)
    {

        $authUser = Auth::user();
        $requestedPermissions = $data['permissions'] ?? [];
        $user = $this->get_user_by_id($userId);
        $authUser_permissions = $this->get_permissions($authUser);

        // فلترة الصلاحيات بحيث نأخذ فقط ما هو موجود عند auth user
        $assignablePermissions = array_filter($requestedPermissions, function ($permission) use ($authUser_permissions) {
            return in_array($permission, $authUser_permissions);
        });

        $authRole = $this->get_role($authUser);
        $targetRole = $this->get_role($user);

        //نتحقق من ما اذا كان المستخدم يستطيع اعطاء هذه الصلاحيات لمستخدم اخر
        $can_assign = $this->canAssign($authRole, $targetRole);

        if (!empty($assignablePermissions) && $can_assign) {

            $user->givePermissionTo($assignablePermissions);
            return $assignablePermissions;
        }
    }


    public function get_role($User)
    {
        return $User->getRoleNames()->first();
    }


    public function get_user_by_id($userId)
    {

        return User::where('id', $userId)->first();
    }


    public function get_permissions($user)
    {
        return $user->getAllPermissions()->pluck('name')->toArray();;
    }



    public function show_permissions()
    {
        $user = Auth::user();
        return $user->getAllPermissions();
    }

    private function canAssign(string $authRole, string $targetRole): bool
    {
        if ($authRole === 'sub_admin') {
            // sub_admin يعدل فقط على المدراء
            return str_starts_with($targetRole, 'Head');
        }

        if (str_starts_with($authRole, 'Head')) {

            // المدير يعدل فقط على موظفي قسمه
            $section = str_replace('Head of ', '', $authRole);
            return $targetRole === $section;
        }

        return false;
    }


    public function remove_permission( $permissionName, int $userId)
    {
        $authUser = Auth::user();
        $targetUser = $this->get_user_by_id($userId);

        // تحقق من الرتب
        $authRole = $this->get_role($authUser);
        $targetRole = $this->get_role($targetUser);

        // تحقق من أن الصلاحية موجودة ضمن model_has_permissions فقط (أي ليست من الرول)
        $hasDirectPermission = DB::table('model_has_permissions')
            ->where('model_id', $targetUser->id)
            ->where('permission_id', function ($query) use ($permissionName) {
                $query->select('id')
                    ->from('permissions')
                    ->where('name', $permissionName)
                    ->limit(1);
            })->exists();

        if ($this->canAssign($authRole, $targetRole) && $hasDirectPermission) {
            // حذف الصلاحية مباشرة
            $targetUser->revokePermissionTo($permissionName);

            return new successResource([]);
        }
        return new failResource(['faild to delete']);
    }
}
