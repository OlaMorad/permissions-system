<?php

namespace App\Services;

use App\Http\Resources\successResource;
use App\Models\Doctor;
use App\Models\Specialization;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DoctorService
{

    public function add_specialization($id)
    {

        $doctor = Auth::user()->doctor;
        $alreadyExists = $doctor->specializations()->where('specialization_id', $id)->exists();

        if ($alreadyExists) {
            return response()->json(['message' => 'هذا التخصص مضاف مسبقًا'], 200);
        }

        $doctor->specializations()->attach($id, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return new successResource([]);
    }
    // بروفايل الامتحان
    public function exam_profile()
    {
        $doctor = Auth::user()->doctor;
        $formattedName = str_replace(' ', '_', $doctor->user->name);
        $candidate = $doctor->candidates()->latest()->first();

        if (!$candidate || !$candidate->exam) {
            return response()->json(['message' => 'لا يوجد ترشيح أو امتحان مرتبط.'], 404);
        }

        $examNumber = $candidate->exam_number;
        $specialization = $candidate->exam->specialization->name ?? 'بدون اختصاص';
        $profileName = "{$formattedName}_{$examNumber}";

        return new successResource([
            'profile_name' => $profileName,
            'specialization' => $specialization,
        ]);
    }
    // عرض الوقت المتبقي للامتحان و تاريخ الامتحان
    public function check_exam_time()
    {
        $doctor = Auth::user()->doctor;
        $candidate = $doctor->candidates()->latest()->first();

        if (!$candidate || !$candidate->exam) {
            return response()->json(['message' => 'لا يوجد ترشيح أو امتحان مرتبط.'], 404);
        }

        $exam = $candidate->exam;
        $examDate = Carbon::parse($exam->date)->toDateString();
        $today = Carbon::today()->toDateString();

        if ($examDate !== $today) {
            return response()->json(['message' => 'اليوم ليس يوم الامتحان.'], 403);
        }

        $now = Carbon::now();
        $startTime = Carbon::parse($exam->start_time);
        $endTime = Carbon::parse($exam->end_time);
        if ($now->gt($endTime)) {
            return response()->json(['message' => 'انتهى وقت الامتحان.'], 403);
        }

        // حساب الوقت المسموح للدخول (ثلث مدة الامتحان)
        // حساب مدة الامتحان بالـ دقائق
        $examDuration  = $startTime->diffInMinutes($endTime);
        $oneThirdDuration  = ceil($examDuration  / 3); // ثلث الوقت
        $entryDeadline = $startTime->copy()->addMinutes($oneThirdDuration);     // آخر وقت مسموح للدخول

        // if ($now->gt($entryDeadline)) {
        //     return response()->json(['message' => 'انتهت مدة الدخول إلى الامتحان'], 403);
        // }

        // can_enter = true إذا الآن >= دقيقة قبل بدء الامتحان و <= ثلث مدة الامتحان بعد البدء
        $oneMinuteBeforeStart = $startTime->copy()->subMinute();
        $canEnter = $now->gte($oneMinuteBeforeStart) && $now->lte($entryDeadline);

        // حساب الوقت المتبقي لبدء الامتحان
        $timeRemaining = $now->lt($startTime)
            ? $startTime->diff($now)->format('%H:%I:%S')
            : '00:00:00';

        $specializationName = $exam->specialization->name ?? 'بدون اختصاص';
        $examTitle = 'امتحان ' . $specializationName;

        return new successResource([
            'exam_name' => $examTitle,
            'exam_date' => $examDate,
            'time_remaining' => $timeRemaining,
            'can_enter' => $canEnter,
        ]);
    }
    // التحقق من كلمة السر الامتحانية
    public function checkExamPassword($request)
    {
        $doctor = Auth::user()->doctor;
        $candidate = $doctor->candidates()->latest()->first();

        if (!$candidate || !$candidate->exam) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد ترشيح أو امتحان مرتبط.'
            ], 404);
        }

        $exam = $candidate->exam;
        $inputPassword = $request->input('password');

        $examPassword = $exam->password()->first();

        if (!$examPassword) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد كلمة سر مخزنة لهذا الامتحان.'
            ], 404);
        }

        if ($examPassword->password === $inputPassword) {
            return response()->json([
                'success' => true,
                'message' => 'كلمة السر صحيحة. يمكنك الدخول.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'كلمة السر غير صحيحة.'
        ], 401);    }
}
