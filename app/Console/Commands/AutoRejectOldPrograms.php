<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Program;
use App\Enums\ExamRequestEnum;
use Carbon\Carbon;

class AutoRejectOldPrograms extends Command
{
    protected $signature = 'programs:auto-reject';

    protected $description = 'Reject programs that are older than one week and still pending';

    public function handle()
    {
        $weekAgo = Carbon::now()->subDays(7);

        $programs = Program::where('approved', ExamRequestEnum::PENDING->value)
            ->where('created_at', '<=', $weekAgo)
            ->get();

        foreach ($programs as $program) {
            $program->update(['approved' => ExamRequestEnum::REJECTED->value]);
        }

        $this->info('تم رفض البرامج التي مر أكثر من أسبوع على إنشائها ولم يتم اتخاذ قرار بشأنها');
    }
}
