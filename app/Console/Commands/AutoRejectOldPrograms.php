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
        // جلب اليوم الحالي و نقصنا منه اسبوع
        $weekAgo = Carbon::now()->subDays(7);
        // البرامج يلي لسا حالتها قيد الدراسة و صرلها مكريتة من أسبوع
        $programs = Program::where('approved', ExamRequestEnum::PENDING->value)
            ->where('created_at', '<=', $weekAgo)
            ->get();
        // منغير حالتها لمرفوضة
        foreach ($programs as $program) {
            $program->update(['approved' => ExamRequestEnum::REJECTED->value]);
        }

        $this->info('تم رفض البرامج التي مر أكثر من أسبوع على إنشائها ولم يتم اتخاذ قرار بشأنها');
    }
}
