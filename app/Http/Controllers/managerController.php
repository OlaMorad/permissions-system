<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Services\ManagerService;
use App\Services\RegisterService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Resources\successResource;
use App\Http\Requests\RegisterManagerRequest;
use App\Models\Role as ModelsRole;

class ManagerController extends Controller
{

    public function __construct(protected RegisterService $registerService, protected ManagerService $managerService) {}


    public function show_roles()
    {
        $roles = ModelsRole::whereNotIn('name', ['الطبيب', 'المدير', 'نائب المدير'])->orderBy('id')
            ->with(['path:id,name'])
            ->get(['id', 'name', 'path_id']);
        return new successResource($roles);
    }


    public function show_my_employees()
    {

        return $response =  $this->managerService->show_my_employees();
    }

    public function show_all_managers()
    {
        $managers_id = dB::table("managers")->pluck('user_id');
        $users = User::whereIn('id', $managers_id)->select('name', 'avatar', 'phone')->get();
        foreach ($users as $user) {
            $user->avatar = asset('storage/' . $user->avatar);
        }
        return $users;
    }
}
