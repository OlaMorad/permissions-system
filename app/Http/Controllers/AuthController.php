<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\successResource;
use App\Services\AccountsManagementService;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected AuthService $authService;
    protected AccountsManagementService $service;

    public function __construct(AuthService $authService, AccountsManagementService $service)
    {
        $this->authService = $authService;
        $this->service = $service;
    }

    // تسجيل الدخول
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('name', 'password');

        return  $response = $this->authService->login($credentials);
    }

    //  تسجيل الخروج
    public function logout()
    {
        $this->authService->logout();

        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }

    // رفريش للتوكن
    public function refresh()
    {
        $token = $this->authService->refresh();

        return response()->json($token);
    }


    public function ResetPassword(ResetPasswordRequest $request, $user_id)
    {


        return $this->service->resetPassword(
            (int) $user_id,
            $request->input('old_password'),
            $request->input('new_password')
        );
    }
}
