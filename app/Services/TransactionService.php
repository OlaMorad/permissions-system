<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Http\Resources\successResource;
use App\Models\ArchiveTransaction;
use App\Models\Transaction;
use App\Models\TransactionMovement;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use App\Services\UserRoleService;
use App\Presenters\TransactionPresenter;
use Carbon\Carbon;

class TransactionService
{
    public function __construct(
        protected UserRoleService $userRoleService,
    ) {}

    // عرض المعاملة و هي معباية
    public function getFormContent(string $transactionUuid): array
    {
        $userRole = $this->userRoleService->getUserRoleName();
        $pathId = $this->userRoleService->getUserPathId();

        $transaction = Transaction::with([
            'content.form.elements',
            'content.elementValues.formElement',
            'content.media'
        ])->where('uuid', $transactionUuid)->firstOrFail();

        //  إذا الطبيب، تأكد إنو المعاملة إله
        if ($userRole === 'الطبيب' && $transaction->content->doctor_id !== Auth::user()->doctor->id) {
            abort(403, 'لا تملك صلاحية عرض هذه المعاملة.');
        }

        //  إذا موظف القسم ومو رئيس، غير الحالة لقيد الدراسة
        if ($transaction->to === $pathId && !$this->userRoleService->isSectionHead($userRole) && $userRole !== 'الطبيب' &&
            $transaction->status_to !== TransactionStatus::UNDER_REVIEW->value) {
            $transaction->update([
                'status_to' => TransactionStatus::UNDER_REVIEW->value,
                'changed_by' => Auth::id(),
            ]);
        }

        $content = $transaction->content;

        return [
            'form_name' => $content->form->name,
            'elements' => $content->elementValues->map(fn($ev) => [
                'label' => $ev->formElement->label,
                'value' => $ev->value,
            ])->values(),
            'media' => $content->media->map(fn($m) => [
                'file' => $m->file ? asset('storage/' . $m->file) : null,
                'image' => $m->image ? asset('storage/' . $m->image) : null,
                'receipt' => asset('storage/' . $m->receipt),
            ])->values(),
        ];
    }

    public function import_for_financial()
    {
        $pathId = $this->userRoleService->getUserPathId();
        $role = $this->userRoleService->getUserRoleName();

        $query = Transaction::where('to', $pathId)
            ->whereNull('from');

        // إذا المستخدم ليس رئيس القسم، لا تعرض "قيد الدراسة"
        if (!$this->userRoleService->isSectionHead($role)) {
            $query->where('status_to', '!=', TransactionStatus::UNDER_REVIEW->value);
        }

        $transactions = $query->with(['content.form', 'content.doctor.user'])->orderBy('received_at')->get();

        return $transactions->map(fn($t) => TransactionPresenter::FinanceImport($t))->values();
    }

    public function export_for_financial()
    {
        $pathId = $this->userRoleService->getUserPathId();

        $transactions = Transaction::where('from', $pathId)
            ->where('sent_at', '>=', now()->subHours(48))
            ->with(['content.form', 'content.doctor.user'])
            ->orderBy('sent_at')
            ->get();
        return $transactions->map(fn($t) => TransactionPresenter::FinanceExport($t))->values();
    }

    public function import_transactions()
    {
        $userPathId = $this->userRoleService->getUserPathId();
        $role = $this->userRoleService->getUserRoleName();

        $query = Transaction::where('to', $userPathId);

        // إذا الموظف مو رئيس القسم، خبي المعاملات قيد الدراسة
        if (!$this->userRoleService->isSectionHead($role)) {
            $query->where('status_to', '!=', TransactionStatus::UNDER_REVIEW->value);
        }

        $transactions = $query
            ->with(['content.form:id,name', 'content.doctor.user:id,name', 'toPath', 'fromPath'])
            ->orderBy('received_at')
            ->get();

        return $transactions->map(fn($t) => TransactionPresenter::forImport($t))->values();
    }

    public function export_transaction()
    {
        $userPathId = $this->userRoleService->getUserPathId();

        $transactions = Transaction::where('from', $userPathId)
            ->where('sent_at', '>=', now()->subHours(48))
            ->with(['content.form:id,name', 'content.doctor.user:id,name', 'toPath', 'fromPath'])
            ->orderBy('sent_at')
            ->get();

        return $transactions->map(fn($t) => TransactionPresenter::forExport($t))->values();
    }

    public function archiveExportedTransactions(): array
    {
        $role = $this->userRoleService->getUserRoleName();

        if (!$this->userRoleService->isSectionHead($role)) {
            abort(403, 'لا تمتلك صلاحيات الوصول للأرشيف');
        }

        $pathId = $this->userRoleService->getUserPathId();


        $transactions = ArchiveTransaction::get()->filter(function ($transaction) use ($pathId) {
            $history = $transaction->status_history;

            if (!is_array($history)) return false;

            foreach ($history as $entry) {
                if (
                    isset($entry['to_path_id'], $entry['status'], $entry['changed_at']) &&
                    (int)$entry['to_path_id'] === (int)$pathId &&
                    in_array($entry['status'], [
                        TransactionStatus::FORWARDED->value,
                        TransactionStatus::REJECTED->value
                    ])
                ) {
                    $changedAt = Carbon::parse($entry['changed_at']);
                        if ($changedAt->diffInHours(now()) >= 48){
                            return true;
                    }
                }
            }

            return false;
        })->values();
        if ($this->userRoleService->isFinancial()) {
            return $transactions->map(fn($t) => TransactionPresenter::ArchiveFinanceExport($t, $pathId))->values()->toArray();
        }

        return $transactions->map(fn($t) => TransactionPresenter::ArchiveForExport($t, $pathId))->values();
    }
}
