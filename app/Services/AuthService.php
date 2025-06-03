<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function login(array $credentials): array|bool
    {
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return false;
        }

        $user = Auth::user();
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ];

        return [
            'access_token' => $token,
            'user' => $userData,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        ];
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
