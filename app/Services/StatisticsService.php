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

        // عدد المعاملات بحالة الانتظار
        $pending = Transaction::where('to', $pathId)
            ->where('status_to', TransactionStatus::PENDING->value)
            ->count();

        // عدد المعاملات بحالة قيد الدراسة
        $underReview = Transaction::where('to', $pathId)
            ->where('status_to', TransactionStatus::UNDER_REVIEW->value)
            ->count();

        // جلب معاملات الأرشيف المتعلقة بالمسار pathId
        $archiveTransactions = ArchiveTransaction::all();

        // عدد المعاملات المحولة من دائرة معينة
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

        // عدد المعاملات المرفوضة في دائرة معينة
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

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // استرجاع كل المعاملات من الأرشيف
        $transactions = ArchiveTransaction::get();

        // نحضّر مصفوفة إحصائية
        $daysStats = array_fill(1, 7, 0);

        foreach ($transactions as $transaction) {
            if (!is_array($transaction->status_history)) continue;

            foreach ($transaction->status_history as $entry) {
                if (
                    isset($entry['to_path_id'], $entry['status'], $entry['changed_at']) &&
                    (int)$entry['to_path_id'] === $pathId &&
                    in_array($entry['status'], [
                        TransactionStatus::FORWARDED->value,
                        TransactionStatus::REJECTED->value
                    ])
                ) {
                    $changedAt = Carbon::parse($entry['changed_at']);
                    if ($changedAt->between($startOfWeek, $endOfWeek)) {
                        $dayOfWeek = $changedAt->dayOfWeekIso;
                        $dayOfWeek = $dayOfWeek === 7 ? 1 : $dayOfWeek + 1; // نجعله يبدأ من الأحد = 1
                        $daysStats[$dayOfWeek]++;
                    }
                }
            }
        }

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
            'total_done' => $daysStats[$dayNumber] ?? 0,
        ])->values()->toArray();
    }

    public function InternalStatistics(){
        $currentUser=Auth::id();
        $manager=Manager::where('user_id',$currentUser)->first();
        if(!$manager){
            return 'ليس من صلاحياتك عرض الاقسام';
        }
        $employeesId=Employee::where('manager_id',$manager->id)->pluck('user_id');
  
      $APPROVED= internalMail::whereIn('from_user_id',$employeesId)->where('status',StatusInternalMail::APPROVED)->count();

      $PENDING= internalMail::whereIn('from_user_id',$employeesId)->where('status',StatusInternalMail::PENDING)->count();

      $REJECTED= internalMail::whereIn('from_user_id',$employeesId)->where('status',StatusInternalMail::REJECTED)->count();
    return [
        'approved' => $APPROVED,
        'pending' => $PENDING,
        'rejected' => $REJECTED
    ];    }
}
