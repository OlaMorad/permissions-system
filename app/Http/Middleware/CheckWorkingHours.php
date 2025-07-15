<?php

namespace App\Http\Middleware;

use App\Models\WorkingHour;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CheckWorkingHours
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->bearerToken() && $request->cookie('jwt_token')) {
            $request->headers->set('Authorization', 'Bearer ' . $request->cookie('jwt_token'));
        }
        $now = Carbon::now();

        $settings = WorkingHour::first() ?? (object)[
            'start_time' => '09:00',
            'end_time'   => '15:00',
            'day_off'    => 'Friday',
        ];

        $startTime = Carbon::createFromTimeString($settings->start_time);
        $endTime   = Carbon::createFromTimeString($settings->end_time);

        $isWithinWorkingHours = $now->between($startTime, $endTime);
        $isWorkingDay = strtolower($now->englishDayOfWeek) !== strtolower($settings->day_off);

        //  إذا خارج الوقت أو يوم عطلة، نتحقق إذا هو أدمن
        if (!$isWithinWorkingHours || !$isWorkingDay) {
            $user = null;

            //  إذا المستخدم مسجل دخول
            if (Auth::check()) {
                $user = Auth::user();
            }

            //  أو إذا هو يحاول يسجل دخول (login)، نستعلم عن المستخدم من البريد
            elseif ($request->is('api/login')) {
                $name = $request->input('name');
                $user = User::where('name', $name)->first();
            }

            //  إذا ما كان عنده صلاحية admin نمنعه
            if (!$user || !$user->hasRole('المدير')) {
                return response()->json([
                    'message' => 'الدخول غير مسموح خارج أوقات الدوام أو يوم العطلة.'
                ], 403);
            }
        }

        return $next($request);
    }
}
