<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Carbon\Carbon;

class VerifySingleSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        try {
            // التحقق من التوكن
            $token = JWTAuth::getToken();
            if (!$token) {
                return response()->json(['message' => 'التوكن غير موجود'], 401);
            }

            // استخراج البيانات من التوكن (الحمولة)
            $payload = JWTAuth::getPayload($token);
            $tokenLoginTime = $payload->get('last_login_at');  // وقت آخر تسجيل دخول من التوكن

            // جلب المستخدم المرتبط بالتوكن
            $user = JWTAuth::authenticate($token);

            if (!$user || !$user->last_login_at) {
                return response()->json(['message' => 'مشكلة في المستخدم أو تاريخ الدخول'], 401);
            }

            // الحصول على وقت آخر تسجيل دخول من قاعدة البيانات
            $dbLoginTime = $user->last_login_at ? Carbon::parse($user->last_login_at)->timestamp : null;

            // التحقق أن التوكن هو الأحدث
            if ($tokenLoginTime !== $dbLoginTime) {
                return response()->json(['message' => 'تم تسجيل دخول جديد، هذا التوكن لم يعد صالحًا'], 401);
            }

            return $next($request);
        } catch (JWTException $e) {
            return response()->json(['message' => 'توكن غير صالح'], 401);
        }
    }
}
