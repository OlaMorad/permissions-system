<?php

namespace App\Console\Commands;

use App\Enums\DoctorExamStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\Exam;
use App\Enums\Program_ExamStatus;

class UpdateExamPresentCandidates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exams:update-present-candidates';
    protected $description = 'Update present candidates count and success rate for finished exams today';

    public function handle()
    {
        $now = Carbon::now();
        $today = $now->toDateString();

        // جلب الامتحانات المنتهية بتاريخ اليوم
        $exams = Exam::whereDate('date', $today)
            ->where('status', Program_ExamStatus::FINISHED->value)
            ->get();

        foreach ($exams as $exam) {
            // عدد المتقدمين الذين لديهم درجة
            $presentCount = $exam->candidates()->whereNotNull('degree')->count();

            // عدد الناجحين
            $passedCount = $exam->candidates()->where('status', DoctorExamStatus::Passed->value)->count();

            // حساب نسبة النجاح (تجنب القسمة على صفر)
            // عدد الناجحين تقسيم عدد المتقدمين *100
            $successRate = $presentCount > 0
                ? round(($passedCount / $presentCount) * 100, 2)
                : 0;

            // تحديث جدول الامتحانات
            // اضافة عدد المتقدمين و نسبة النجاح لجدول الامتحانات
            $exam->update([
                'present_candidates_count' => $presentCount,
                'success_rate' => $successRate,
            ]);

            $this->info("امتحان #{$exam->id} | المتقدمين: {$presentCount} | نسبة النجاح: {$successRate}%");
        }

        $this->info("Successfully updated {$exams->count()} exams.");
    }
}
