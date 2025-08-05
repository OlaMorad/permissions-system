<?php

namespace App\Http\Controllers;

use App\Http\Requests\CodeRequest;
use App\Http\Requests\DoctorLoginRequest;
use Illuminate\Http\Request;
use App\Services\DoctorAuthService;
use App\Http\Requests\DoctorRegisterRequest;
use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\SetNewPasswordRequest;
use App\Http\Requests\VerifyRegisterCodeRequest;

class DoctorAuthController extends Controller
{
    public function __construct(protected DoctorAuthService $doctor) {}
    public function register(DoctorRegisterRequest $request)
    {
        return $this->doctor->pre_register($request);
    }

        public function verify_register_code(VerifyRegisterCodeRequest $request)
    {

        return $this->doctor->verify_register_code($request);
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

    public function set_password(SetNewPasswordRequest $request){
                  return $this->doctor->set_password($request);

    }
    public function deactivate_account()
    {
        return $this->doctor->deactivateAccount();
    }
    public function doctor_profile()
    {
        return $this->doctor->doctor_profile();
    }
}

