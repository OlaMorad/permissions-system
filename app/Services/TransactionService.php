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
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;

class TransactionService
{
    public function __construct(
        protected UserRoleService $userRoleService,
    ) {}

    // عرض المعاملة و هي معباية في حال كانت بقسم الوارد
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
        if (
            $transaction->to === $pathId && !$this->userRoleService->isSectionHead($userRole) && $userRole !== 'الطبيب' &&
            $transaction->status_to !== TransactionStatus::UNDER_REVIEW->value
        ) {
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
                'type' => $ev->formElement->type->value,
                'value' => $ev->value,
            ])->values(),
            'media' => $content->media->map(fn($m) => [
                'file' => $m->file ? asset('storage/' . $m->file) : null,
                'image' => $m->image ? asset('storage/' . $m->image) : null,
                'receipt' => asset('storage/' . $m->receipt),
            ])->values(),
        ];
    }
    // عرض محتوى المعاملة اذا كانت بقسم الصادر
    public function show_transaction_content($uuid)
    {
        $transaction = ArchiveTransaction::where('uuid', $uuid)->first();

        if (!$transaction) {
            return response()->json([
                'message' => 'لم يتم العثور على المعاملة',
            ], 404);
        }

        $content = $transaction->transaction_content;

        return new successResource([
            'form_name' => $content['form_name'] ?? '',
            'elements' => array_map(fn($e) => Arr::only($e, ['label', 'value', 'type']), $content['elements'] ?? []),
            'media' => array_map(function ($m) {
                return [
                    'file' => !empty($m['file']) ? asset('storage/' . $m['file']) : null,
                    'image' => !empty($m['image']) ? asset('storage/' . $m['image']) : null,
                    'receipt' => !empty($m['receipt']) ? asset('storage/' . $m['receipt']) : null,
                ];
            }, $content['media'] ?? []),
        ]);
    }
    // المعاملات الواردة لقسم المالية
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
    // المعاملات الواردة الى اي قسم
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
            ->with(['content.form:id,name', 'content.doctor.user:id,name,phone,avatar', 'toPath', 'fromPath'])
            ->orderBy('received_at')
            ->get();

        return $transactions->map(fn($t) => TransactionPresenter::forImport($t))->values();
    }
    // المعاملات  يلي تم تغيير حالتها لمحولة او مرفوضة في قسم ما
    public function export_transactions(): array
    {
        $pathId = $this->userRoleService->getUserPathId();
        $isFinance = $this->userRoleService->isFinancial();

        $transactions = $this->getExternalTransactions($pathId, true); //  خلال آخر 48 ساعة

        return $transactions
            ->map(
                fn($t) =>
                $isFinance
                    ? TransactionPresenter::ArchiveFinanceExport($t, $pathId)
                    : TransactionPresenter::ArchiveForExport($t, $pathId)
            )
            ->values()->toArray();
    }
    // المعاملات يلي صرلها محولة او مرفوضة من قسم ما 48 ساعة
    public function archiveExportedTransactions(): array
    {
        $role = $this->userRoleService->getUserRoleName();

        if (!$this->userRoleService->isSectionHead($role)) {
            abort(403, 'لا تمتلك صلاحيات الوصول للأرشيف');
        }

        $pathId = $this->userRoleService->getUserPathId();
        $isFinance = $this->userRoleService->isFinancial();

        $transactions = $this->getExternalTransactions($pathId, false); //  أقدم من 48 ساعة

        return $transactions
            ->map(
                fn($t) =>
                $isFinance
                    ? TransactionPresenter::ArchiveFinanceExport($t, $pathId)
                    : TransactionPresenter::ArchiveForExport($t, $pathId)
            )
            ->values()->toArray();
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
                    in_array($entry['status'], [
                        TransactionStatus::FORWARDED->value,
                        TransactionStatus::REJECTED->value,
                    ])
                ) {
                    $changed_at = Carbon::parse($entry['changed_at']);
                    $hours_change = $changed_at->diffInHours(now());

                    return $isRecent ? $hours_change < 48 : $hours_change >= 48;
                }
            }
            return false;
        })->values();
    }
}
