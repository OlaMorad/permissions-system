<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProgramExamRequest;
use App\Http\Resources\successResource;
use App\Services\ProgramService;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function __construct(
        protected ProgramService $programService,
    ) {}

    public function store(ProgramExamRequest $request)
    {
        $program = $this->programService->create_Program($request->validated());
        return new successResource($program);
    }
    public function index()
    {
        return $this->programService->get_all_programs();
    }

    public function show_program_details($id)
    {
        return $this->programService->show_program_details($id);
    }
}
