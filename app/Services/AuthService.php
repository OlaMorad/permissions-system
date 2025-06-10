<?php

namespace App\Services;

use App\Http\Resources\failResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\successResource;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;


class AuthService
{
    public function login(array $credentials)
    {
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return new failResource("بيانات الدخول غير صحيحة");
        }

        $user = Auth::user();
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ];
        $user->last_login_at = now();
        $user->save();
        return new successResource([
            'access_token' => $token,
            'user' => $userData,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        ]);

    }

    public function logout(): void
    {
        Auth::guard()->logout();
    }

    public function refresh(): array
    {
        return [
            'access_token' => Auth::refresh(),
        ];
    }
}
