<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkingHoursRequest;
use App\Http\Resources\successResource;
use App\Models\WorkingHour;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function updateWorkingHours(WorkingHoursRequest $request)
    {
        $data = $request->validated();

        $workingHours = WorkingHour::updateOrCreate([],$data);

        new successResource([$workingHours,'تم تحديث أوقات الدوام بنجاح.']);

    }
}
