<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Services\FormCreationService;
use App\Services\ManualFormInputService;
use App\Services\WordFormInputService;
use App\Models\Form;

class FormFactoryService
{
    public function __construct(protected FormCreationService $creator) {}

    public function createFromWord(Request $request): Form
    {
        $file = $request->file('file');
        $inputService = new WordFormInputService($file->getRealPath());

        return $this->creator->create_Form(
            $inputService,
            $file->getClientOriginalName(),
            (float) $request->input('cost'),
            $request->input('path_ids')
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
