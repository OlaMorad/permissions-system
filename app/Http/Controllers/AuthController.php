<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Requests\LoginRequest;

use App\Services\AccountsManagementService;

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

        return  $this->authService->login($credentials,$request);
    }

    //  تسجيل الخروج
    public function logout(Request $request)
    {
        $this->authService->logout($request);

        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }

    // رفريش للتوكن
    public function refresh(Request $request)
    {

        $token = $this->authService->refresh($request);

        return response()->json($token);
    }

}
