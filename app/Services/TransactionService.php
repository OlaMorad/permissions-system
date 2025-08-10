<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Http\Resources\successResource;
use App\Models\ArchiveTransaction;
use App\Models\Employee;
use App\Models\Path;
use App\Models\Transaction;
use App\Models\TransactionMovement;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use App\Services\UserRoleService;
use App\Presenters\TransactionPresenter;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(
        protected UserRoleService $userRoleService,
    ) {}

    // عرض المعاملة و هي معباية في حال كانت بقسم الوارد
    public function getFormContent(string $transactionUuid): array
    {
        $userRole = $this->userRoleService->getUserRoleName();

        $transaction = Transaction::with([
            'content.form.elements',
            'content.elementValues.formElement',
            'content.media'
        ])->where('uuid', $transactionUuid)->firstOrFail();

        if ($userRole === 'الطبيب' && $transaction->content->doctor_id !== Auth::user()->doctor->id) {
            abort(403, 'لا تملك صلاحية عرض هذه المعاملة.');
        }
        $content = $transaction->content;

        return [
            'form_name' => $content->form->name,
            'elements' => TransactionPresenter::formatElements($content->elementValues),
            'media' => TransactionPresenter::formatMedia($content->media),
        ];
    }
    // عرض محتوى المعاملة اذا كانت بقسم الصادر
    public function show_transaction_content($uuid)
    {
        $transaction = ArchiveTransaction::where('uuid', $uuid)->firstOrFail();

        $content = $transaction->transaction_content;

        return new successResource([
            'form_name' => $content['form_name'] ?? '',
            'elements' => TransactionPresenter::formatArchivedElements($content['elements'] ?? []),
            'media' => TransactionPresenter::formatArchivedMedia($content['media'] ?? []),
        ]);
    }
    // المعاملات الواردة لقسم المالية
    public function import_for_financial()
    {
        $pathId = $this->userRoleService->getUserPathId();
        $employee_id = Employee::where('user_id', Auth::id())->value('id');

        $query = Transaction::where('to', $pathId)->whereNull('from');

        // الموظفين فقط يخضعون لفلترة المعاملات قيد الدراسة
        if ($this->userRoleService->isEmployee()) {
            $query->where(function ($q) use ($employee_id) {
                $q->where('status_to', '!=', TransactionStatus::UNDER_REVIEW) // كل الحالات غير "قيد الدراسة"
                    ->orWhere(function ($q2) use ($employee_id) {
                        $q2->where('status_to', TransactionStatus::UNDER_REVIEW) // "قيد الدراسة"
                            ->where('changed_by', $employee_id); // ولكن هو من غير حالتها
                    });
            });
        }

        return TransactionPresenter::FinanceImportList(
            $query->with(['content.form', 'content.doctor.user'])->orderBy('received_at')->get()
        );
    }

    public function import_transactions()
    {
        $userPathId = $this->userRoleService->getUserPathId();
        $employee_id = Employee::where('user_id', Auth::id())->value('id');

        $query = Transaction::where('to', $userPathId);
        // اذا كان المستخدم موظف ما بيقدر يشوف المعاملات يلي بحالة قيد الدراسة
        if ($this->userRoleService->isEmployee()) {
            $query->where(function ($q) use ($employee_id) {
                $q->where('status_to', '!=', TransactionStatus::UNDER_REVIEW) // كل الحالات غير "قيد الدراسة"
                    ->orWhere(function ($q2) use ($employee_id) {
                        $q2->where('status_to', TransactionStatus::UNDER_REVIEW) // "قيد الدراسة"
                            ->where('changed_by', $employee_id); // ولكن هو من غير حالتها
                    });
            });
        }

        return TransactionPresenter::forImportList(
            $query->with(['content.form:id,name', 'content.doctor.user:id,name,phone,avatar', 'toPath', 'fromPath'])
                ->orderBy('received_at')->get()
        );
    }

    public function export_transactions(): array
    {
        $pathId = $this->userRoleService->getUserPathId();
        $isFinance = $this->userRoleService->isFinancial();

        $transactions = $this->getExternalTransactions($pathId, true);
        return TransactionPresenter::exportList($transactions, $pathId, $isFinance);
    }

    public function archiveExportedTransactions(): array
    {
        $role = $this->userRoleService->getUserRoleName();

        if (
            !$this->userRoleService->isSectionHead($role) &&
            !$this->userRoleService->isManager($role)
        ) {
            abort(403, 'غير مصرح لك بعرض هذه الإحصائيات.');
        }

        $pathId = $this->userRoleService->getUserPathId();
        $isFinance = $this->userRoleService->isFinancial();

        $transactions = $this->getExternalTransactions($pathId, false);
        return TransactionPresenter::exportList($transactions, $pathId, $isFinance);
    }

    public function getExternalTransactions(int $pathId, bool $isRecent): Collection
    {
        return ArchiveTransaction::get()->filter(function ($transaction) use ($pathId, $isRecent) {
            $history = $transaction->status_history;
            if (!is_array($history)) return false;

            foreach ($history as $entry) {
                if (
                    isset($entry['to_path_id'], $entry['status'], $entry['changed_at']) &&
                    (int)$entry['to_path_id'] === $pathId &&
                    in_array($entry['status'], [TransactionStatus::FORWARDED->value, TransactionStatus::REJECTED->value])
                ) {
                    $hours = Carbon::parse($entry['changed_at'])->diffInHours(now());
                    return $isRecent ? $hours < 48 : $hours >= 48;
                }
            }
            return false;
        })->sortBy(function ($transaction) {
            $lastChange = collect($transaction->status_history)
                ->sortByDesc('changed_at')
                ->first();

            return $lastChange['changed_at'] ?? null;
        })
            ->values();
    }

    public function getArchiveTransactionsByPath(int $pathId): array
    {
        $isFinance = $this->userRoleService->isFinancialPath($pathId);

        // استخدم الدالة الجاهزة مع فلتر الزمن (false = أقدم من 48 ساعة)
        $transactions = $this->getExternalTransactions($pathId, false);

        $path = Path::find($pathId);

        return [
            'path' => [
                'id' => $path?->id,
                'name' => $path?->name,
            ],
            'transactions' => TransactionPresenter::exportList($transactions, $pathId, $isFinance),
        ];
    }

    // الارشيف الكلي
    public function show_archive()
    {
        $transactions = ArchiveTransaction::where('final_status', 'منجزة')->get();

        return TransactionPresenter::exportList($transactions, null, false);
    }
    // عرض صورة الوصل من تيبل المعاملات
    public function get_receipt_image($uuid)
    {
        $transaction = Transaction::where('uuid', $uuid)->with('content')->first();

        if (!$transaction || !$transaction->content) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }
        $receipt = DB::table('form_media')
            ->where('form_content_id', $transaction->content->id)->latest()
            ->value('receipt');
        $receipt_image = $receipt ? asset('storage/' . $receipt) : null;
        return new successResource($receipt_image);
    }
    // عرض الصورة من الارشيف
    public function archived_receipt_image($uuid)
    {
        $transaction = ArchiveTransaction::where('uuid', $uuid)->first();
        $content = $transaction->transaction_content;
        $media = $content['media'] ?? [];
        foreach ($media as $item) {
            if (!empty($item['receipt'])) {
                $receipt_image = asset('storage/' . $item['receipt']);
                return new successResource($receipt_image);
            }
        }
    }
}
