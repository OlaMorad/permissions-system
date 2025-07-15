<?php

namespace App\Jobs;

use App\Imports\QuestionsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ImportQuestionsFromExcel implements ShouldQueue
{
    use Queueable;

   protected  $path;
    protected int $specializationId;

    public function __construct( $path, int $specializationId)
    {
        $this->path = $path;
        $this->specializationId = $specializationId;
    }

    public function handle(): void
    {
           $fullPath = storage_path("app/" . $this->path);

        Excel::import(new QuestionsImport($this->specializationId), $this->path);
                // حذف الملف المؤقت بعد الاستيراد
        Storage::delete($this->path);
    }
}
