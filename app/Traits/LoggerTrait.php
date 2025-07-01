<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait LoggerTrait
{
    public function logInfo($message, array $context = [])
    {
        Log::info($message, $context);
    }

    public function logError($message, array $context = [])
    {
        Log::error($message, $context);
    }

    public function logWarning($message, array $context = [])
    {
        Log::warning($message, $context);
    }
}

