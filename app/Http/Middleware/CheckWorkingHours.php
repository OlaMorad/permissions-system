<?php

namespace App\Http\Middleware;

use App\Models\WorkingHour;
use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CheckWorkingHours
{
    public function handle(Request $request, Closure $next)
    {

        // ✅ إحضار السجل أو استخدام قيم افتراضية
        $settings = WorkingHour::first() ?? (object)[
            'start_time' => '08:00',
            'end_time' => '15:00',
            'day_off'    => 'Friday',        ];
        $now = Carbon::now();

        if (strtolower($now->englishDayOfWeek) === strtolower($settings->day_off)) {
            return response()->json([
                'message' => 'الدخول غير مسموح يوم العطلة (' . $settings->day_off . ').'
            ], 403);
        }

        $startTime = Carbon::createFromTimeString($settings->start_time);
        $endTime   = Carbon::createFromTimeString($settings->end_time);

        if (!$now->between($startTime, $endTime)) {
            return response()->json([
                'message' => "الدخول غير مسموح خارج أوقات الدوام ({$settings->start_time} حتى {$settings->end_time})."
            ], 403);
        }

        return $next($request);
    }
}
