<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\EmailVerification;

class ClearExpiredEmailVerifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email-verifications:clear-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'حذف جميع سجلات التحقق من البريد الإلكتروني التي انتهت صلاحيتها';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deleted = EmailVerification::where('expires_at', '<', Carbon::now())->delete();

        $this->info("تم حذف $deleted من السجلات المنتهية.");
    }
}
