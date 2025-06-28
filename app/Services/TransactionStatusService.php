<?php

namespace App\Services;

use App\Models\Transaction;
use App\Enums\TransactionStatus;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\successResource;
use App\Enums\StatusInternalMail;

class TransactionStatusService
{
    private function getUserPathId(): ?int
    {
        $user = Auth::user();
        $roleName = $user->getRoleNames()->first();
        $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();

        return $role?->path_id;
    }

    public function completeTransaction(int $transactionId)
    {
        $pathId = $this->getUserPathId();
        $transaction = Transaction::where('to', $pathId)->findOrFail($transactionId);

        $transaction->status_to = TransactionStatus::COMPLETED->value;
        $transaction->save();

        return new successResource(['message' => 'تم تحديث حالة المعاملة إلى مكتملة']);
    }

    public function approve_receipt(string $uuid)
    {
        $transaction = Transaction::where('uuid', $uuid)->firstOrFail();

        // تأكد إنه المستخدم بالمكان الصحيح لتنفيذ هذا التحديث
        $pathId = $this->getUserPathId();
        if ($transaction->to !== $pathId) {
            abort(403, 'لا تملك الصلاحية لتحديث هذه المعاملة.');
        }

        $transaction->update([
            'receipt_status' => StatusInternalMail::APPROVED->value,
            'status_to' => TransactionStatus::COMPLETED->value,
        ]);

        return new successResource(['message' => 'تمت الموافقة على الإيصال وتحديث حالة المعاملة']);
    }
}
