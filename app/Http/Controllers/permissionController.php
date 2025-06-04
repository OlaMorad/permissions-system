<?php

namespace App\Http\Controllers;

use App\Http\Requests\permissionRequest;
use App\Http\Resources\failResource;
use App\Http\Resources\successResource;
use App\Services\permissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class permissionController extends Controller
{

    public function __construct(protected permissionService $service) {}



    public function add_permission(permissionRequest $request, $userId)
    {

        $respone = $this->service->add_permission($request->validated(), $userId);
        if ($respone) {
            return new successResource([
                'permissions has added' => $respone
            ]);
        }
        return new failResource(["the permission is not accept"]);
    }

    public function show_my_permissions()
    {
        $respone = $this->service->show_permissions();
        return new successResource([
            'my_permissions' => $respone
        ]);
    }

    public function remove_permission(permissionRequest $request, $userId){
     return $this->service->remove_permission($request->validated(), $userId);

    }
}
