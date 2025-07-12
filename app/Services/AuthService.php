<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Resources\failResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\successResource;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;


class AuthService
{
public function login(array $credentials,$request)
{
    if (!$token = Auth::guard('api')->attempt($credentials)) {
        return new failResource("بيانات الدخول غير صحيحة");
    }

    $user = Auth::user();
    $user->last_login_at = now();
    $user->save();

    $userData = [
        'name' => $user->name,
        'avatar' => asset('storage/' . $user->avatar),
    ];

    // إعادة توليد التوكن مع claim مخصص
    $token = JWTAuth::claims([
        'last_login_at' => Carbon::parse($user->last_login_at)->timestamp,
    ])->fromUser($user);

    // فحص هل يجب إرسال التوكن في كوكي
    $useCookie = $request->header('X-Use-Cookie') === 'true';

    $responseData = [
        'user' => $userData,
        'roles' => $user->getRoleNames(),
    ];

    if ($useCookie) {
        return response()->json(new successResource($responseData))
            ->cookie(
                'jwt_token',
                $token,
                60 * 24, // دقيقة
                '/',
                null, // النطاق (يمكنكِ تغييره حسب الدومين)
                true,  // Secure (HTTPS)
                true,  // HttpOnly
                false, // raw
                'Strict' // SameSite
            );
    }

    // الوضع العادي: نرجع التوكن ضمن JSON
    return new successResource(array_merge($responseData, [
        'access_token' => $token,
    ]));
}


    public function logout( $request)
    {
        Auth::guard()->logout();
           if ($request->header('X-Use-Cookie') === 'true') {

        return response()->json(['message' => 'تم تسجيل الخروج'])->cookie('jwt_token', '', -1);
    }
    return response()->json(['message' => 'تم تسجيل الخروج']);
    }

    public function refresh(): array
    {
        return [
            'access_token' => Auth::refresh(),
        ];
    }
}
