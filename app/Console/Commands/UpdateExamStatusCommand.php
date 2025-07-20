<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\Exam;
use App\Enums\Program_ExamStatus;

class UpdateExamStatusCommand extends Command
{
    protected $signature = 'exams:update-status';
    protected $description = 'Update exam status only based on start and end time';

    public function handle()
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        // 1. تفعيل الامتحانات اللي بلش وقتها ولسه ما اتفعلت
        $examsToActivate = Exam::whereDate('date', $today)
            ->whereTime('start_time', '<=', $currentTime)
            ->where('status', Program_ExamStatus::PENDING->value)
            ->get();

        foreach ($examsToActivate as $exam) {
            $exam->status = Program_ExamStatus::ACTIVE->value;
            $exam->save();
        }

        // 2. إنهاء الامتحانات اللي خلص وقتها ولسه مخلصة
        $examsToFinish = Exam::whereDate('date', $today)
            ->whereTime('end_time', '<=', $currentTime)
            ->where('status', Program_ExamStatus::ACTIVE->value)
            ->get();

        foreach ($examsToFinish as $exam) {
            $exam->status = Program_ExamStatus::FINISHED->value;
            $exam->save();
        }

        $this->info("Activated exams: " . $examsToActivate->count());
        $this->info("Finished exams: " . $examsToFinish->count());
    }
}
