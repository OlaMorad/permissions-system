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

        // البرامج التي تبدأ اليوم
        $programsToActivate = Program::whereDate('start_date', $today)
            ->where('approved', ExamRequestEnum::APPROVED->value)
            ->get();

        foreach ($programsToActivate as $program) {
            $program->status = Program_ExamStatus::ACTIVE->value;
            $program->save();
        }

        // البرامج التي تنتهي اليوم
        $programsToFinish = Program::whereDate('end_date', $today)
            ->where('approved', ExamRequestEnum::APPROVED->value)
            ->get();

        foreach ($programsToFinish as $program) {
            $program->status = Program_ExamStatus::FINISHED->value;
            $program->save();
        }

        $this->info(" Activated Programs: " . $programsToActivate->count());
        $this->info(" Finished Programs: " . $programsToFinish->count());
    }
}
