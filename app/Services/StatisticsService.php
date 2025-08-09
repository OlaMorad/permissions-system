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
use App\Models\Path;
use Spatie\Permission\Models\Role;
use App\Models\TransactionMovement;
use Illuminate\Support\Facades\Auth;

class StatisticsService
{
    public function __construct(
        protected UserRoleService $userRoleService
    ) {}
    // ========== إحصائيات لكل دائرة ==========
    public function AllPathsAchievementStatistics(): array
    {
        $paths = Path::whereNotIn('name', ['الوزارة', 'المدير'])->get();
        $active = Transaction::all();
        $archived = ArchiveTransaction::all();

        $results = [];

        foreach ($paths as $path) {
            $pathId = $path->id;

            $pending = $active->where('to', $pathId)
                ->where('status_to', TransactionStatus::PENDING->value)
                ->count();

            $underReview = $active->where('to', $pathId)
                ->where('status_to', TransactionStatus::UNDER_REVIEW->value)
                ->count();

            $done = $this->countTransactionsArchive($archived, $pathId, [
                TransactionStatus::FORWARDED->value,
                TransactionStatus::REJECTED->value
            ]);

            $total = $pending + $underReview + $done;
            $percentage = $total > 0 ? round(($done / $total) * 100, 2) : 0;

            $results[] = [
                'الدائرة' => $path->name,
                'نسبة الانجاز المئوية' => $percentage
            ];
        }

        return $results;
    }

    // ========== إحصائيات الدائرة الحالية لرئيس القسم ==========
    public function ExternalStatistics(): array
    {
        $role = $this->userRoleService->getUserRoleName();
        if (
            !$this->userRoleService->isSectionHead($role) &&
            !$this->userRoleService->isManager($role)
        ) {
            abort(403, 'غير مصرح لك بعرض هذه الإحصائيات.');
        }

        $pathId = $this->userRoleService->getUserPathId();

        $pending = Transaction::where('to', $pathId)
            ->where('status_to', TransactionStatus::PENDING->value)
            ->count();

        $underReview = Transaction::where('to', $pathId)
            ->where('status_to', TransactionStatus::UNDER_REVIEW->value)
            ->count();

        $archived = ArchiveTransaction::all();

        $forwarded = $this->countTransactionsArchive($archived, $pathId, [TransactionStatus::FORWARDED->value]);
        $rejected = $this->countTransactionsArchive($archived, $pathId, [TransactionStatus::REJECTED->value]);

        $done = $forwarded + $rejected;
        $total = $done + $pending + $underReview;

        return [
            'total' => $total,
            'done' => $done,
            'pending' => $pending,
            'under_review' => $underReview,
        ];
    }

    // ========== الانجاز الأسبوعي للدائرة الحالية ==========
    public function weeklyDoneStatistics(): array
    {
        $role = $this->userRoleService->getUserRoleName();
        if (
            !$this->userRoleService->isSectionHead($role) &&
            !$this->userRoleService->isManager($role)
        ) {
            abort(403, 'غير مصرح لك بعرض هذه الإحصائيات.');
        }

        $pathId = $this->userRoleService->getUserPathId();
        $transactions = ArchiveTransaction::all();
        $daysStats = $this->WeeklyDoneByPath($transactions, $pathId);

        return $this->formatDays($daysStats);
    }

    // ========== الانجاز الأسبوعي لدائرة محددة ==========
    public function WeeklyStatisticsForPath(int $pathId): array
    {
        $path = Path::findOrFail($pathId);
        $transactions = ArchiveTransaction::all();
        $daysStats = $this->WeeklyDoneByPath($transactions, $pathId);

        return [
            'الدائرة' => $path->name,
            'الانجاز الاسبوعي' => $this->formatDays($daysStats),
        ];
    }

    // عدّ عدد المعاملات المؤرشفة التي حققت شرط الحالة والمسار
    protected function countTransactionsArchive($transactions, int $pathId, array $statuses): int
    {
        return $transactions->filter(function ($transaction) use ($pathId, $statuses) {
            if (!is_array($transaction->status_history)) return false;

            foreach ($transaction->status_history as $entry) {
                if (
                    isset($entry['to_path_id'], $entry['status']) &&
                    (int)$entry['to_path_id'] === $pathId &&
                    in_array($entry['status'], $statuses)
                ) {
                    return true;
                }
            }
            return false;
        })->count();
    }

    // احسب الانجاز الاسبوعي حسب الأيام لدائرة معينة
    protected function WeeklyDoneByPath($transactions, int $pathId): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
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
                        $dayOfWeek = $dayOfWeek === 7 ? 1 : $dayOfWeek + 1;
                        $daysStats[$dayOfWeek]++;
                    }
                }
            }
        }

        return $daysStats;
    }

    // تحويل أرقام الأيام إلى أسماء عربية
    protected function formatDays(array $daysStats): array
    {
        $daysMap = [
            1 => 'الأحد',
            2 => 'الإثنين',
            3 => 'الثلاثاء',
            4 => 'الأربعاء',
            5 => 'الخميس',
            6 => 'الجمعة',
            7 => 'السبت',
        ];
        return collect($daysMap)->map(function ($dayName, $dayNumber) use ($daysStats) {
            return [
                'day' => $dayName,
                'total_done' => $daysStats[$dayNumber] ?? 0,
            ];
        })->values()->toArray();
    }
    // احصائيات البريد الداخلي
    public function InternalStatistics()
    {
        $currentUser = Auth::id();
        $manager = Manager::where('user_id', $currentUser)->first();
        if (!$manager) {
            return 'ليس من صلاحياتك عرض الاقسام';
        }
        $employeesId = Employee::where('manager_id', $manager->id)->pluck('user_id');

        $APPROVED = internalMail::whereIn('from_user_id', $employeesId)->where('status', StatusInternalMail::APPROVED)->count();

        $PENDING = internalMail::whereIn('from_user_id', $employeesId)->where('status', StatusInternalMail::PENDING)->count();

        $REJECTED = internalMail::whereIn('from_user_id', $employeesId)->where('status', StatusInternalMail::REJECTED)->count();
        return [
            'approved' => $APPROVED,
            'pending' => $PENDING,
            'rejected' => $REJECTED
        ];
    }


        public function InternalStatisticsForAdmin()
    {
        $APPROVED = internalMail::where('status', StatusInternalMail::APPROVED)->count();

        $PENDING = internalMail::where('status', StatusInternalMail::PENDING)->count();

        $REJECTED = internalMail::where('status', StatusInternalMail::REJECTED)->count();
        return [
            'approved' => $APPROVED,
            'pending' => $PENDING,
            'rejected' => $REJECTED,
            'total'=> $APPROVED + $PENDING+ $REJECTED
        ];
    }

}
