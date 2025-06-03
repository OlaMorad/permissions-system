<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Manager;
use Illuminate\Http\Request;
use App\Services\RegisterService;
use App\Http\Requests\UserRequest;
use Spatie\Permission\Models\Role;
use App\Http\Resources\successResource;
use App\Http\Requests\RegisterManagerRequest;

class ManagerController extends Controller
{

    public function __construct(protected RegisterService $managerService) {}


    public function create_manager(RegisterManagerRequest $request, $role_id)
    {
        $this->managerService->registerManager($request->validated());

        return new successResource([]);
    }

    public function ManagerRoles()
    {
        $roles = Role::where('name', 'like', 'Head of%')->get(['id', 'name']);
        return response()->json([
            'roles' => $roles
        ]);

    }
}
