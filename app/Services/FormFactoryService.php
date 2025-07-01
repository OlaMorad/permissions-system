<?php

namespace App\Services;

use App\Jobs\ProcessWordFormJob;
use Illuminate\Http\Request;
use App\Services\FormCreationService;
use App\Services\ManualFormInputService;
use App\Services\WordFormInputService;
use App\Models\Form;

class FormFactoryService
{
    public function __construct(protected FormCreationService $creator) {}

    public function createFromWord(Request $request): void
    {
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $storedPath = "files/{$filename}";

        $file->move(storage_path('app/files'), $filename);

        ProcessWordFormJob::dispatch(
            $storedPath,
            $file->getClientOriginalName(),
            (float) $request->input('cost'),
            $request->input('path_ids', [])
        );
    }

    public function createFromManual(Request $request): Form
    {
        $data = $request->validated();
        $inputService = new ManualFormInputService($data);

        return $this->creator->create_Form(
            $inputService,
            $inputService->getName(),
            (float) $data['cost'],
            $inputService->getPathIds()
        );
    }
}
