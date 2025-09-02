<?php

namespace App\Console\Commands;

use App\Models\Exam;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Services\FirebaseNotificationService;

class SendExamPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:send-password';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send password 5 minutes before exam start';
    protected FirebaseNotificationService $firebase;

    public function __construct(FirebaseNotificationService $firebase)
    {
        parent::__construct();
        $this->firebase = $firebase;
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // جلب كل الامتحانات مع الاختصاص
        $exams = Exam::with('specialization')->get();

        foreach ($exams as $exam) {
            $start = Carbon::parse($exam->start_time);
            $diff = $start->diffInMinutes($now, false);
            if ($diff === 5) {
                $password = Str::random(8); // توليد كلمة سر 8 محارف
                echo "كلمة السر لأمتحان {$exam->specialization->name}: $password\n";


                $title = "كلمة سر الامتحان: {$exam->specialization->name}";
                $body = "كلمة السر الخاصة بك: $password";

                $data = [
                    'exam_id' => $exam->id,
                    'password' => $password,
                ];
                $this->firebase->sendToRole('رئيس الامتحانات', $title, $body, $data);
            }
        }
    }
}
