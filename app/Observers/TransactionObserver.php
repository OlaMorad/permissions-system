<?php

namespace App\Observers;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Enums\TransactionStatus;
use Illuminate\Support\Carbon;
use App\Models\TransactionMovement;
use Illuminate\Support\Facades\Auth;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {

        if (!$transaction->wasChanged('status_to')) {
            return;
        }

        $userId = Auth::id(); // المستخدم الذي قام بالفعل

        if ($transaction->status_to === TransactionStatus::REJECTED) {

            Transaction::where('id', $transaction->id)->update([
                'from' => $transaction->to,
                'to' => null,
                'status_from' => TransactionStatus::REJECTED->value,
                'status_to' => null,
                'sent_at' => now(),
            ]);
            TransactionMovement::create([
                'transaction_id' => $transaction->id,
                'from_path_id' => $transaction->to,
                'to_path_id' => null,
                'status' => TransactionStatus::REJECTED->value,
                'changed_by' => $userId,
                'changed_at' => now(),
            ]);
            return;
        }

        if ($transaction->status_to === TransactionStatus::FORWARDED) {
            $this->moveToNextStep($transaction, $userId);
            return;
        }
    }

    /**
     * جبلي المسار التالي تبع المعاملة
     */
    private function moveToNextStep(Transaction $transaction, $userId): void
    {

        $current = $transaction->to;
        $form = $transaction->content->form;

        $steps = $form->paths()->pluck('path_id')->toArray();
        $index = array_search($current, $steps);

        $next = $steps[$index + 1] ?? null;
        if ($next) {
            // احفظ الحركة قبل التحديث
            TransactionMovement::create([
                'transaction_id' => $transaction->id,
                'from_path_id' => $current,
                'to_path_id' => $next,
                'status' => TransactionStatus::FORWARDED->value,
                'changed_by' => $userId,
                'changed_at' => now(),
            ]);
            $this->updateTransaction($transaction, $current, $next);
        }
    }

    /**
     * عدل معلومات المعاملة وحالتها عند كل مسار
     */
    private function updateTransaction(Transaction $transaction, $current, $next): void
    {

        Transaction::where('id', $transaction->id)->update([
            'from' => $current,
            'to' => $next,
            'sent_at' => now(),
            'status_from' => TransactionStatus::FORWARDED->value,
            'status_to' => TransactionStatus::PENDING->value,
        ]);
    }


    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
}
