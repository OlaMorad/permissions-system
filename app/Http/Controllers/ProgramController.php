<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProgramExamRequest;
use App\Http\Requests\UpdateProgramStatusRequest;
use App\Http\Resources\successResource;
use App\Services\ProgramService;
use App\Services\ProgramStatusService;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function __construct(
        protected ProgramService $programService,
        protected ProgramStatusService $programStatusService

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
    public function get_approved_programs()
    {
        return $this->programService->get_approved_programs();
    }
    public function show_program_details($id)
    {
        return $this->programService->show_program_details($id);
    }
    public function update_status(UpdateProgramStatusRequest $request, $id)
    {
        return $this->programStatusService->updateApprovalStatus($id, $request->approved);
    }
}
