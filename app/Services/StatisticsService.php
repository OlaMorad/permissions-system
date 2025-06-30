<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionMovement;
use App\Enums\TransactionStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class StatisticsService
{
    private function getUserPathId(): ?int
    {
        $user = Auth::user();
        $roleName = $user->getRoleNames()->first();
        $role = Role::where('name', $roleName)->first();

        return $role?->path_id;
    }

    public function ExternalStatistics(): array
    {
        $pathId = $this->getUserPathId();

        // عدد المعاملات بحالة PENDING
        $pending = Transaction::where('to', $pathId)
            ->where('status_to', TransactionStatus::PENDING->value)
            ->count();

        // عدد المعاملات بحالة UNDER_REVIEW
        $underReview = Transaction::where('to', $pathId)
            ->where('status_to', TransactionStatus::UNDER_REVIEW->value)
            ->count();

        // عدد المعاملات التي مرت على الدائرة
        $moved = TransactionMovement::where('to_path_id', $pathId)->count();

        // عدد المعاملات المحولة
        $forwarded = TransactionMovement::where('from_path_id', $pathId)
            ->where('status', TransactionStatus::FORWARDED->value)
            ->count();

        // عدد المعاملات المرفوضة
        $rejected = TransactionMovement::where('from_path_id', $pathId)
            ->where('status', TransactionStatus::REJECTED->value)
            ->count();

        // المنتهية = محولة + مرفوضة
        $done = $forwarded + $rejected;

        $total = $moved + $pending + $underReview;

        return [
            'total' => $total,
            'done' => $done,
            'rejected' => $rejected,
            'under_review' => $underReview,
        ];
    }

    public function weeklyDoneStatistics(): array
    {
        $pathId = $this->getUserPathId();

        if (!$pathId) {
            return [];
        }

        $doneMovements = TransactionMovement::whereIn('status', [
            TransactionStatus::FORWARDED->value,
            TransactionStatus::REJECTED->value,
        ])
            ->where('from_path_id', $pathId)
            ->whereBetween('changed_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])
            ->selectRaw("DAYOFWEEK(changed_at) as day_of_week, COUNT(*) as total")
            ->groupBy('day_of_week')
            ->pluck('total', 'day_of_week');

        $daysMap = collect([
            1 => 'الأحد',
            2 => 'الإثنين',
            3 => 'الثلاثاء',
            4 => 'الأربعاء',
            5 => 'الخميس',
            6 => 'الجمعة',
            7 => 'السبت',
        ]);

        return $daysMap->map(fn($dayName, $dayNumber) => [
            'day' => $dayName,
            'total_done' => $doneMovements[$dayNumber] ?? 0,
        ])->values()->toArray();
    }

    public function employeeStatistics(): array
    {
        return TransactionMovement::with('changedBy')
            ->whereIn('status', [
                TransactionStatus::FORWARDED->value,
                TransactionStatus::REJECTED->value
            ])
            ->selectRaw('changed_by, COUNT(*) as total')
            ->groupBy('changed_by')
            ->get()
            ->map(function ($movement) {
                return [
                    'employee_id' => $movement->changed_by,
                    'employee_name' => $movement->changedBy->name ?? 'غير معروف',
                    'handled_transactions' => $movement->total,
                ];
            })->toArray();
    }
}
