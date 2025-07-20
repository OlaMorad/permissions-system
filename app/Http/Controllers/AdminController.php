<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkingHoursRequest;
use App\Http\Resources\successResource;
use App\Models\WorkingHour;
use App\Services\AdminService;
use App\Services\WorkingHoursService;

class AdminController extends Controller
{
    public function __construct(
        protected WorkingHoursService $workingHoursService , protected AdminService $admin
    ) {}
    public function updateWorkingHours(WorkingHoursRequest $request)
    {
        $data = $request->validated();
        $updated = $this->workingHoursService->update($data);

        return new successResource([
            'message' => 'تم تحديث أوقات الدوام بنجاح',
            'data' => $updated,
        ]);
    }

    public function showWorkingHours()
    {
        $result = $this->workingHoursService->get();

        return new successResource($result);
    }


    public function show_employees_by_path($path){
      return $this->admin->show_employees_by_path($path);
    }
}
