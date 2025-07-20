<?php

namespace App\Console\Commands;

use App\Enums\Program_ExamStatus;
use Illuminate\Console\Command;
use App\Models\Exam;
use Carbon\Carbon;

class UpdateExamCandidateCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exams:update-candidates-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update candidates count for exams that are pending and within one week';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $targetDate = $today->copy()->addWeek(); // بعد أسبوع بالضبط

        // جلب الامتحانات بالحالة انتظار وتاريخها بعد أسبوع بالضبط
        $exams = Exam::where('status', Program_ExamStatus::PENDING)
            ->whereDate('date', $targetDate)
            ->get();
        // منحسب عدد المرشحين لهي الامتحانات و منخزنو بتيبل الامتحانات
        foreach ($exams as $exam) {
            $count = $exam->candidates()->count();
            $exam->update([
                'candidates_count' => $count
            ]);
            $this->info("الامتحان رقم: {$exam->id} | عدد المرشحين: {$count}");
        }
        $this->info("تم تحديث عدد المرشحين في {$exams->count()} من الامتحانات.");
    }
}
