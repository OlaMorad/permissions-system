<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterManagerRequest;
use App\Http\Requests\UserRequest;
use App\Models\Manager;
use App\Models\User;
use App\Services\RegisterService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class ManagerController extends Controller
{
    // public function create_manager(UserRequest $request, $roleName)
    // {
    //     $user = User::create_user($request);
    //     $role = Role::where('name', $roleName)->firstOrFail();
    //     Manager::create([
    //         'user_id' => $user->id,
    //         'role_id' => $role->id,
    //     ]);

    //     return response()->json(['message' => 'تم إنشاء المدير بنجاح']);
    // }
    public function __construct(protected RegisterService $managerService) {}

    public function create_manager(RegisterManagerRequest $request, $role_id)
    {
        $user = $this->managerService->registerManager($request->validated());

        return response()->json([
            'message' => 'تم إنشاء حساب رئيس القسم بنجاح',
            // 'manager' => $user->load('roles')->getRoleNames()
        ]);
    }

    public function ManagerRoles()
    {
        $roles = Role::where('name', 'like', 'Head of%')->get(['id', 'name']);
        return response()->json([
            'roles' => $roles
        ]);
    }
}
