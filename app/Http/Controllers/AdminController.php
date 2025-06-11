<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkingHoursRequest;
use App\Http\Resources\successResource;
use App\Models\WorkingHour;

class AdminController extends Controller
{
    public function updateWorkingHours(WorkingHoursRequest $request)
    {
        $data = $request->validated();

        $workingHours = WorkingHour::updateOrCreate(['id'=>1],$data);

       return new successResource([
            $workingHours
        ]);

    }
}
