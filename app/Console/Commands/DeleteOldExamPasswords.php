<?php

namespace App\Console\Commands;

use App\Models\ExamPassword;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOldExamPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:delete-old-passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'حذف كلمات السر التي مضى على إنشائها ساعتين';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $old_password = Carbon::now()->subHours(2);

        $deleted = ExamPassword::where('created_at', '<', $old_password)->delete();

        $this->info("تم حذف $deleted كلمة سر قديمة.");
    }
}
