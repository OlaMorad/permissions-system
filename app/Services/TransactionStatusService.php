<?php

namespace App\Services;

use App\Models\Transaction;
use App\Enums\TransactionStatus;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\successResource;
use App\Enums\StatusInternalMail;
use Spatie\Permission\Models\Role;

class TransactionStatusService
{
    public function __construct(
        protected UserRoleService $userRoleService,
    ) {}

    private function getAuthorizedTransaction(string $uuid): Transaction
    {
        $pathId = $this->userRoleService->getUserPathId();

        $transaction = Transaction::where('uuid', $uuid)->firstOrFail();

        if ($transaction->to !== $pathId) {
            abort(403, 'لا تملك الصلاحية لتحديث هذه المعاملة.');
        }

        return $transaction;
    }

    public function forward_transaction(string $uuid)
    {
        $transaction = $this->getAuthorizedTransaction($uuid);

        // تحديث الحالة إلى "محولة"
        $transaction->status_to = TransactionStatus::FORWARDED->value;
        $transaction->save();

        return new successResource(['message' => 'تم تحويل المعاملة إلى المسار التالي.']);
    }

    public function reject_transaction(string $uuid)
    {
        $transaction = $this->getAuthorizedTransaction($uuid);

        $transaction->update([
            'status_to' => TransactionStatus::REJECTED->value,
        ]);

        return new successResource(['message' => 'تم رفض المعاملة بنجاح.']);
    }


    public function approve_receipt(string $uuid)
    {
        $transaction = $this->getAuthorizedTransaction($uuid);

        $transaction->status_to = TransactionStatus::FORWARDED->value;
        $transaction->receipt_status = StatusInternalMail::APPROVED->value;
        $transaction->save();

        return new successResource(['message' => 'تمت الموافقة على الإيصال وتحديث حالة المعاملة']);
    }

    public function reject_receipt(string $uuid)
    {
        $transaction = $this->getAuthorizedTransaction($uuid);

        $transaction->status_to = TransactionStatus::REJECTED->value;
        $transaction->receipt_status = StatusInternalMail::REJECTED->value;
        $transaction->save();

        return new successResource(['message' => 'تم رفض الإيصال وتحديث حالة المعاملة إلى مرفوضة']);
    }
}
