<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Doctor;
use Illuminate\Support\Str;
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
//ما قبل انشاء الحساب نرسل كود التحقق بعدين اذا دخله صح ننشئ حسابه
public function pre_register($request)
{
    $email = $request->email;

    // توليد كود تحقق 4 خانات
    $code = rand(1000, 9999);

    EmailVerification::updateOrCreate(
        ['email' => $email],
        [
            'code' => $code,
            'data' => json_encode($request->only(['name', 'email', 'phone', 'password'])),
            'expires_at' => now()->addMinutes(10),
        ]
    );

    forgetPasswordJob::dispatch($email, $code);

    return new successResource(['message' => 'تم إرسال كود التحقق إلى بريدك الإلكتروني.']);
}

public function verify_register_code($request)
{
    $email = $request['email'];
    $code = $request['code'];

    $verification = EmailVerification::where('email', $email)
        ->where('code', $code)
        ->where('expires_at', '>=', now())
        ->first();

    if (!$verification) {
        return response()->json(['message' => 'الكود غير صحيح أو منتهي الصلاحية.'], 422);
    }

    // استعادة البيانات من json
    $data = json_decode($verification->data);

    // أنشئ الحساب
    $avatarDefault = 'avatars/2vrjbanTePmk7v0vMBaZNthsdZCDqVEqHYQV3xW4.jpg';
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

    // حذف سجل التحقق بعد الاستخدام
    $verification->delete();

    return new successResource(['message' => 'تم إنشاء الحساب بنجاح.']);
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
        $resetToken = Str::random(64);
        // حفظ الكود في الجدول مع صلاحية 10 دقائق
        EmailVerification::updateOrCreate(
            ['email' => $email],
            [
                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes(10),
                'reset_token' => $resetToken,
            ]
        );

        // إرسال الكود عبر البريد الإلكتروني
        forgetPasswordJob::dispatch($email, $code);

        return new successResource([
            'تم إرسال رمز التحقق إلى بريدك الإلكتروني.',
            'reset_token' => $resetToken
        ]);
    }

    public function put_code($request)
    {
        $resetToken = $request->reset_token;
        $code = $request->code;

        // جلب السجل الذي يطابق البريد والكود ويكون غير منتهي الصلاحية
        $verification = EmailVerification::where('reset_token', $resetToken)
            ->where('code', $code)
            ->where('expires_at', '>=', Carbon::now())
            ->first();

        if (!$verification) {
            return response()->json([
                'message' => 'الكود غير صحيح أو منتهي الصلاحية.'
            ], 422);
        }

        return new successResource([]);
    }

    public function set_password($request)
    {
        $resetToken = $request->reset_token;

        $verification = EmailVerification::where('reset_token', $resetToken)->first();

        if (!$verification || $verification->expires_at < Carbon::now()) {
            return response()->json(['message' => 'رمز الاستعادة غير صالح أو منتهي.'], 404);
        }
        $user = User::where('email', $verification->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        $verification->delete();

        return new successResource(['تم تحديث كلمة المرور بنجاح']);
    }
}
