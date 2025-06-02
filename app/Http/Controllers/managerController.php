<?php

namespace App\Http\Controllers;


use App\Http\Requests\UserRequest;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class ManagerController extends Controller
{
    public function create_manager(UserRequest $request, $roleName)
    {
        $user = User::create_user($request);
        $role = Role::where('name', $roleName)->firstOrFail();
        Manager::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);

        return response()->json(['message' => 'تم إنشاء المدير بنجاح']);
    }



}
