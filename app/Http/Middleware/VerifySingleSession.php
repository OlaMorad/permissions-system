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
    if ($request->is('api/refresh')) {
        return $next($request);
    }

    try {
        // ✅ جلب التوكن سواء من الهيدر أو الكوكي
        $token = $request->bearerToken();
        // dd($token);
// dd( $token==null && $request->hasCookie('jwt_token'));
        if ((!$token || $token === 'null') && $request->hasCookie('jwt_token')) {
            $token = $request->cookie('jwt_token');

        }

        if (!$token) {
            return response()->json(['message' => 'التوكن غير موجود'], 401);
        }

        // ✅ تمرير التوكن يدويًا
        JWTAuth::setToken($token);

        $payload = JWTAuth::getPayload();
        $tokenLoginTime = $payload->get('last_login_at');
        $user = JWTAuth::authenticate();

        if (!$user || !$user->last_login_at) {
            return response()->json(['message' => 'مشكلة في المستخدم أو تاريخ الدخول'], 401);
        }

        if (!$user->is_active) {
            abort(403, 'حسابك غير مفعل حالياً.');
        }

        $dbLoginTime = Carbon::parse($user->last_login_at)->timestamp;

        if ($tokenLoginTime !== $dbLoginTime) {
            return response()->json(['message' => 'تم تسجيل دخول جديد، هذا التوكن لم يعد صالحًا'], 401);
        }

        return $next($request);
    } catch (JWTException $e) {
        return response()->json(['message' => 'توكن غير صالح'], 401);
    }
}

}
