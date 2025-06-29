<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Manager;
use App\Models\Employee;
use App\Models\Transaction;
use App\Models\internalMail;
use App\Enums\TransactionStatus;
use App\Enums\StatusInternalMail;
use Spatie\Permission\Models\Role;
use App\Models\TransactionMovement;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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

    public function InternalStatistics(){
        $currentUser=Auth::id();
        $manager=Manager::where('user_id',$currentUser)->first();
        if(!$manager){
            return 'ليس من صلاحياتك عرض الاقسام';
        }
        $employeesId=Employee::where('manager_id',$manager->id)->pluck('user_id');
         // تحديد بداية ونهاية الأسبوع (من 7 أيام حتى اليوم)
    $startDate = Carbon::now()->subDays(7)->startOfDay();
    $endDate = Carbon::now()->endOfDay();
      $APPROVED= internalMail::whereIn('from_user_id',$employeesId)->where('status',StatusInternalMail::APPROVED)
      ->whereBetween('created_at', [$startDate, $endDate])->count();

      $PENDING= internalMail::whereIn('from_user_id',$employeesId)->where('status',StatusInternalMail::PENDING)
      ->whereBetween('created_at', [$startDate, $endDate])->count();

      $REJECTED= internalMail::whereIn('from_user_id',$employeesId)->where('status',StatusInternalMail::REJECTED)
      ->whereBetween('created_at', [$startDate, $endDate])->count();
    return [
        'approved' => $APPROVED,
        'pending' => $PENDING,
        'rejected' => $REJECTED
    ];    }
}
