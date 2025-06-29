<?php

namespace App\Http\Controllers;

use App\Http\Resources\successResource;
use App\Services\StatisticsService;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function __construct(
        protected StatisticsService $Service
    ) {}

    public function ExternalStatisticsSummary()
    {
        $data = $this->Service->ExternalStatistics();
        return new successResource($data);
    }

    public function employeePerformance()
    {
        $data = $this->Service->employeeStatistics();
       return new successResource($data);
    }
}
