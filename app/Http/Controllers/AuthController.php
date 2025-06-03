<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
        // تسجيل الدخول
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('name', 'password');

        $response = $this->authService->login($credentials);

        if (!$response) {
            return response()->json(['error' => 'بيانات الدخول غير صحيحة'], 401);
        }

        return response()->json(['data'=> $response,'message'=>'تم تسجيل الدخول بنجاح'],200);
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
}
