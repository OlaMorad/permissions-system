<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StatisticsService;
use App\Http\Resources\failResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\successResource;

class StatisticsController extends Controller
{
    public function __construct(
        protected StatisticsService $Service
    ) {}
    public function AllPathsAchievementStatistics()
    {
        return new successResource($this->Service->AllPathsAchievementStatistics());
    }
    public function ExternalStatisticsSummary()
    {
        $data = $this->Service->ExternalStatistics();
        return new successResource($data);
    }

    public function weeklyDone()
    {
        $data = $this->Service->weeklyDoneStatistics();
        return new successResource($data);
    }

    public function weeklyDoneByPath($pathId)
    {
        $data = $this->Service->WeeklyStatisticsForPath((int) $pathId);
        return new successResource($data);
    }

    public function InternalStatisticsSummary(){

        $data = $this->Service->InternalStatistics();
           // إذا كانت القيمة String، نعيد FailResource
    if (is_string($data)) {
        return new failResource($data);
    }
         return new successResource($data);
    }

     public function InternalStatisticsForAdmin(){

        $data = $this->Service->InternalStatisticsForAdmin();
           // إذا كانت القيمة String، نعيد FailResource
    if (is_string($data)) {
        return new failResource($data);
    }
         return new successResource($data);
    }
}
