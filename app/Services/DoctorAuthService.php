<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Doctor;
use App\Jobs\forgetPasswordJob;
use App\Models\EmailVerification;
use App\Http\Resources\failResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\successResource;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class DoctorAuthService
{
    public function register($data)
    {

        $avatarDefault = 'avatars\2vrjbanTePmk7v0vMBaZNthsdZCDqVEqHYQV3xW4.jpg';
        $user = User::create([
            'name' => $data->name,
            'phone' => $data->phone,
            'email' => $data->email,
            'avatar' => $avatarDefault,
            'address' => '',
            'password' => Hash::make($data->password),
        ]);

        $user->assignRole('الطبيب');

        Doctor::create([
            'user_id' => $user->id,
        ]);

        return new successResource([]);
    }


    public function login(array $credentials)
    {
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return new failResource("الاسم او كلمة المرور غير صحيحين");
        }

        $user = Auth::user();
        $user->last_login_at = now();
        $user->save();

        $userData = [
            'name' => $user->name,
            'avatar' => asset('storage/' . $user->avatar),
        ];

        $token = JWTAuth::claims([
            'last_login_at' => Carbon::parse($user->last_login_at)->timestamp,
        ])->fromUser($user);

        $responseData = [
            'user' => $userData,
            'roles' => $user->getRoleNames(),
        ];
        return new successResource(array_merge($responseData, [
            'access_token' => $token,
        ]));
    }

    public function forget_password($request)
    {
        $email = $request->email;

        // توليد كود 4 خانات عشوائي
        $code = rand(1000, 9999);

        // حفظ الكود في الجدول مع صلاحية 10 دقائق
        EmailVerification::updateOrCreate(
            ['email' => $email],
            [
                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );

        // إرسال الكود عبر البريد الإلكتروني
    forgetPasswordJob::dispatch($email, $code);

        return new successResource(['تم إرسال رمز التحقق إلى بريدك الإلكتروني.']);
    }

    public function put_code($request)
    {
        $email = $request->email;
        $code = $request->code;

        // جلب السجل الذي يطابق البريد والكود ويكون غير منتهي الصلاحية
        $verification = EmailVerification::where('email', $email)
            ->where('code', $code)
            ->where('expires_at', '>=', Carbon::now())
            ->first();

        if (!$verification) {
            return response()->json([
                'message' => 'الكود غير صحيح أو منتهي الصلاحية.'
            ], 422);
        }
    $doctor = User::where('email', $email)->first();

    if (! $doctor) {
        return response()->json(['message' => 'لا يوجد حساب مرتبط بهذا البريد الإلكتروني'], 404);
    }

    $doctor->password = Hash::make($request->password);
    $doctor->save();


        $verification->delete();
        return new successResource([]);
    }

}
