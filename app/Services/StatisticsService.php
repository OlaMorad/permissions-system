<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Manager;
use App\Models\Employee;
use App\Models\Transaction;
use App\Models\internalMail;
use App\Enums\TransactionStatus;
use App\Enums\StatusInternalMail;
use App\Models\ArchiveTransaction;
use Spatie\Permission\Models\Role;
use App\Models\TransactionMovement;
use Illuminate\Support\Facades\Auth;

class StatisticsService
{
    public function __construct(
        protected UserRoleService $userRoleService
    ) {}

    public function ExternalStatistics(): array
    {
        $role = $this->userRoleService->getUserRoleName();
        if (!$this->userRoleService->isSectionHead($role)) {
            abort(403, 'غير مصرح لك بعرض هذه الإحصائيات.');
        }
        $pathId = $this->userRoleService->getUserPathId();

        // عدد المعاملات بحالة PENDING
        $pending = Transaction::where('to', $pathId)
            ->where('status_to', TransactionStatus::PENDING->value)
            ->count();

        // عدد المعاملات بحالة UNDER_REVIEW
        $underReview = Transaction::where('to', $pathId)
            ->where('status_to', TransactionStatus::UNDER_REVIEW->value)
            ->count();

        // جلب معاملات الأرشيف المتعلقة بالمسار pathId
        $archiveTransactions = ArchiveTransaction::all();

        // احصاء المحولة (FORWARDED) حسب status_history و to_path_id
        $forwarded = $archiveTransactions->filter(function ($transaction) use ($pathId) {
            if (!is_array($transaction->status_history)) return false;

            foreach ($transaction->status_history as $entry) {
                if (
                    isset($entry['to_path_id'], $entry['status']) &&
                    (int)$entry['to_path_id'] === $pathId &&
                    $entry['status'] === TransactionStatus::FORWARDED->value
                ) {
                    return true;
                }
            }
            return false;
        })->count();

        // احصاء المرفوضة (REJECTED) بنفس الطريقة
        $rejected = $archiveTransactions->filter(function ($transaction) use ($pathId) {
            if (!is_array($transaction->status_history)) return false;

            foreach ($transaction->status_history as $entry) {
                if (
                    isset($entry['to_path_id'], $entry['status']) &&
                    (int)$entry['to_path_id'] === $pathId &&
                    $entry['status'] === TransactionStatus::REJECTED->value
                ) {
                    return true;
                }
            }
            return false;
        })->count();

        // المنتهية = محولة + مرفوضة
        $done = $forwarded + $rejected;
        $total = $done + $pending + $underReview ;

        return [
            'total' => $total,
            'done' => $done,
            'rejected' => $rejected,
            'under_review' => $underReview,
        ];
    }

    public function weeklyDoneStatistics(): array
    {
        $role = $this->userRoleService->getUserRoleName();
        if (!$this->userRoleService->isSectionHead($role)) {
            abort(403, 'غير مصرح لك بعرض هذه الإحصائيات.');
        }

        $pathId = $this->userRoleService->getUserPathId();

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
