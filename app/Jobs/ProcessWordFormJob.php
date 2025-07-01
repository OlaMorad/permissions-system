<?php

namespace App\Jobs;

use App\Services\WordFormInputService;
use App\Services\FormCreationService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Throwable;

class ProcessWordFormJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected string $originalName;
    protected float $cost;
    protected array $pathIds;

    public function __construct(string $filePath, string $originalName, float $cost, array $pathIds)
    {
        $this->filePath = $filePath;
        $this->originalName = $originalName;
        $this->cost = $cost;
        $this->pathIds = $pathIds;
    }

    public function handle(FormCreationService $creator): void
    {
        $localPath = storage_path("app/{$this->filePath}");

        if (!file_exists($localPath)) {
            throw new Exception("الملف غير موجود: {$localPath}");
        }

        try {
            $inputService = new WordFormInputService($localPath);

            $creator->create_Form(
                $inputService,
                $this->originalName,
                $this->cost,
                $this->pathIds
            );

            Storage::delete($this->filePath);
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
