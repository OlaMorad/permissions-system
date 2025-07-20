<?php

namespace App\Console\Commands;

use App\Models\Program;
use App\Enums\ExamRequestEnum;
use App\Enums\Program_ExamStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class UpdateProgramStatusCommand extends Command
{
    protected $signature = 'programs:activate';

    protected $description = 'Activate programs starting today and finish programs ending today, if approved';

    public function handle()
    {
        $today = Carbon::today();

        //  البرامج التي تبدأ اليوم وتمت الموافقة عليها
        $programsToActivate = Program::whereDate('start_date', $today)
            ->where('approved', ExamRequestEnum::APPROVED->value)
            ->get();
        // منغير حالتها لفعالة
        foreach ($programsToActivate as $program) {
            $program->status = Program_ExamStatus::ACTIVE->value;
            $program->save();
        }

        // البرامج التي تنتهي اليوم، شرط أن تكون حالتها "فعالة"
        $programsToFinish = Program::whereDate('end_date', $today)
            ->where('approved', ExamRequestEnum::APPROVED->value)
            ->where('status', Program_ExamStatus::ACTIVE->value)
            ->get();
        // منغير حالتها لمنتهية
        foreach ($programsToFinish as $program) {
            $program->status = Program_ExamStatus::FINISHED->value;
            $program->save();
        }
        // بيعرض شو البرامج يلي تغيرت حالتها لفعالة او منتهية
        if ($programsToActivate->isEmpty()) {
            $this->info("No programs activated today.");
        } else {
            $this->info("Activated Program IDs: " . $programsToActivate->pluck('id')->implode(', '));
        }

        if ($programsToFinish->isEmpty()) {
            $this->info("No programs finished today.");
        } else {
            $this->info("Finished Program IDs: " . $programsToFinish->pluck('id')->implode(', '));
        }
    }
}
