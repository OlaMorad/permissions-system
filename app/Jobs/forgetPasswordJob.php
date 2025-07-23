<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class forgetPasswordJob implements ShouldQueue
{
    use Queueable;
    protected $email;
    protected $code;

    /**
     * Create a new job instance.
     */
  public function __construct($email, $code)
    {
        $this->email = $email;
        $this->code = $code;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::html("رمز التحقق الخاص بك هو: {$this->code}", function ($message) {
            $message->to($this->email)
                ->subject('رمز التحقق لاستعادة كلمة المرور');
        });
    }
}
