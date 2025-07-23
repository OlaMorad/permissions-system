<?php

namespace App\Http\Controllers;

use App\Http\Requests\CodeRequest;
use App\Http\Requests\DoctorLoginRequest;
use Illuminate\Http\Request;
use App\Services\DoctorAuthService;
use App\Http\Requests\DoctorRegisterRequest;
use App\Http\Requests\ForgetPasswordRequest;

class DoctorAuthController extends Controller
{
    public function __construct(protected DoctorAuthService $doctor) {}
    public function register(DoctorRegisterRequest $request)
    {
        return $this->doctor->register($request);
    }

    public function login(DoctorLoginRequest $request)
    {
                $credentials = $request->only('phone', 'password');

        return $this->doctor->login($credentials);
    }

    public function forget_password(ForgetPasswordRequest $request){
return $this->doctor->forget_password($request);
    }

    public function put_code(CodeRequest $request){
        return $this->doctor->put_code($request);
    }
    
}
