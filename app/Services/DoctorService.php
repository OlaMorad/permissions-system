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
    // بروفايلو بالامتحان
    public function exam_profile()
    {
        $doctor = Auth::user()->doctor;
        // الفراغات استبدلها ب _
        $formattedName = str_replace(' ', '_', $doctor->user->name);
        // اخر ترشيح للدكتور
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
        if ($now->lt($startTime)) {
            $timeRemaining = $startTime->diff($now)->format('%H:%I:%S');
        } else {
            $timeRemaining = '00:00:00';
        }

        $examNumber = $candidate->exam_number;
        $specialization = $candidate->exam->specialization->name ?? 'بدون اختصاص';

        $profileName = "{$formattedName}_{$examNumber}";

        return new successResource([
            'profile_name' => $profileName,
            'specialization' => $specialization,
            'exam_date' => $examDate,
            'time_remaining' => $timeRemaining,
        ]);
    }
}
