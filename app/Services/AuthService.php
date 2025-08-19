<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Resources\failResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\successResource;
use Exception;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;


class AuthService
{
public function login(array $credentials,$request)
{
    if (!$token = Auth::guard('api')->attempt($credentials)) {
        return new failResource("الاسم او كلمة المرور غير صحيحين");
    }

    $user = Auth::user();
    $user->last_login_at = now();
    $user->save();

        if ($request->filled('device_token')) {
        \App\Models\DeviceToken::updateOrCreate(
            ['user_id' => $user->id],
            ['device_token' => $request->device_token]
        );
    }
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

    public function checkSession(Request $request)
    {
        try {
            // هل التوكن موجود في الكوكي أم في الهيدر؟
            $useCookie = $request->header('X-Use-Cookie') === 'true';

            // جلب التوكن
            $token = $useCookie
                ? $request->cookie('jwt_token')
                : $request->bearerToken();

            // التوكن غير موجود
            if (!$token) {
                return response()->json([
                    'authenticated' => false,
                    'error' => 'لم يتم إرسال التوكن'
                ], 401);
            }

            // محاولة التحقق من التوكن وجلب المستخدم
            $user = JWTAuth::setToken($token)->authenticate();

            // التوكن غير صالح أو المستخدم غير موجود
            if (!$user) {
                return response()->json([
                    'authenticated' => false,
                    'error' => 'المستخدم غير موجود أو التوكن غير صالح'
                ], 401);
            }

            // تحضير بيانات المستخدم
            $userData = [
                'name'   => $user->name,
                'avatar' => asset('storage/' . $user->avatar),
                'roles'  => $user->getRoleNames(),
            ];

            // تم التحقق بنجاح
            return response()->json([
                'authenticated' => true,
                'user' => $userData
            ], 200);
        } catch (Exception $e) {
            // تخصيص رسالة الخطأ إذا كانت بسبب انتهاء صلاحية التوكن
            $message = str_contains($e->getMessage(), 'expired')
                ? 'انتهت صلاحية التوكن الخاص بك'
                : 'حدث خطأ أثناء التحقق من الجلسة';

            return response()->json([
                'authenticated' => false,
                'error' => $message
            ], 401);
        }
    }

    public function logout(Request $request)
{
    try {
        $useCookie = $request->header('X-Use-Cookie') === 'true';

        // جلب التوكن يدويًا
        $token = $useCookie
            ? $request->cookie('jwt_token')
            : $request->bearerToken();
//dd(  JWTAuth::setToken($token)->invalidate());
        if (!$token) {
            return response()->json([
                'message' => 'لا يوجد توكن لتسجيل الخروج'
            ], 400);
        }

        // تمرير التوكن يدويًا لأننا ما عدنا نستخدم auth:api
        JWTAuth::setToken($token)->invalidate();

        // حذف الكوكي إذا كان مستخدم
        if ($useCookie) {
            return response()->json(['message' => 'تم تسجيل الخروج'])
                ->cookie('jwt_token', '', -1);
        }

        return response()->json(['message' => 'تم تسجيل الخروج']);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'فشل تسجيل الخروج',
            'error' => $e->getMessage()
        ], 500);
    }
}



public function refresh( $request)
{
    try {
        $useCookie = $request->header('X-Use-Cookie') === 'true';

        // استخراج التوكن سواء من الكوكي أو الهيدر
        $token = $useCookie
            ? $request->cookie('jwt_token')
            : $request->bearerToken(); // Authorization: Bearer token

        if (!$token) {
            return response()->json(['message' => 'لا يوجد توكن'], 401);
        }

        // مررنا التوكن يدويًا إلى JWTAuth
        $newToken = JWTAuth::setToken($token)->refresh();


        if ($useCookie) {
            return response()->json([
                'message' => 'refreshed'
            ])
            ->cookie(
                'jwt_token',
                $newToken,
                60 * 24,
                '/',
                null,
                false,  // ⚠️ خليها false للتجريب محلياً (لو على localhost)
                true,
                false,
                'Lax'   // بدل Strict لتجريب أسهل
            );
        }

        return response()->json([
            'access_token' => $newToken,
        ]);
    }
  catch (\Exception $e) {
    return response()->json([
        'message' => 'انتهت الجلسة أو التوكن غير صالح',
        'error' => $e->getMessage(), // هذا يكشف لكِ الخطأ الحقيقي
    ], 401);
}

}

}
