<?php

namespace App\Services;

use App\Models\Transaction;
use App\Enums\TransactionStatus;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\successResource;
use App\Enums\StatusInternalMail;
use App\Http\Resources\failResource;
use App\Http\Requests\ReceiptStatusRequest;
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

    public function updateTransactionStatus(string $uuid, TransactionStatus $requestedStatus)
    {
        $transaction = $this->getAuthorizedTransaction($uuid);

        if (!in_array($requestedStatus, [TransactionStatus::FORWARDED, TransactionStatus::REJECTED])) {
            return new failResource(['الحالة غير صالحة. يجب أن تكون "محول" أو "مرفوض".']);
        }

        $transaction->status_to = $requestedStatus->value;
        $transaction->save();

        return new successResource([
            'message' => $requestedStatus === TransactionStatus::FORWARDED
                ? 'تم تحويل المعاملة إلى المسار التالي.'
                : 'تم رفض المعاملة بنجاح.',
        ]);
    }

    public function updateReceiptStatus(ReceiptStatusRequest $request)
    {
        $uuid = $request->input('uuid');
        $requestedStatus = $request->input('status');

        $transaction = $this->getAuthorizedTransaction($uuid);

        if ($requestedStatus === StatusInternalMail::APPROVED->value)
         {
            $transaction->receipt_status = StatusInternalMail::APPROVED->value;
            $transaction->status_to = TransactionStatus::FORWARDED->value;
            $message = 'تمت الموافقة على الإيصال وتحديث حالة المعاملة.';
        }
         elseif ($requestedStatus === StatusInternalMail::REJECTED->value)
         {
            $transaction->receipt_status = StatusInternalMail::REJECTED->value;
            $transaction->status_to = TransactionStatus::REJECTED->value;
            $message = 'تم رفض الإيصال وتحديث حالة المعاملة إلى مرفوضة.';
        } else
        {
            return new failResource(['الحالة غير صالحة. يجب أن تكون "مرسلة" أو "مرفوضة".']);
        }

        $transaction->save();

        return new successResource(['message' => $message]);
    }
}
