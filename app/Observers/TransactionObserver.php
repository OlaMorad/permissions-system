<?php

namespace App\Observers;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Enums\TransactionStatus;
use Illuminate\Support\Carbon;

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
        if ($this->isCompleted($transaction)) {
            $this->moveToNextStep($transaction);
        }
    }
    // اذا تغيرت حالة المعاملة بالمسار الحالي لمكتملة 
    private function isCompleted(Transaction $transaction): bool
    {
        return $transaction->wasChanged('status_to')
            && $transaction->status_to === TransactionStatus::COMPLETED;
    }
    // جبلي المسار التالي تبع المعاملة 
    private function moveToNextStep(Transaction $transaction): void
    {
        $current = $transaction->to;
        $form = $transaction->content->form;

        $steps = $form->paths()->pluck('path_id')->toArray();
        $index = array_search($current, $steps);

        $next = $steps[$index + 1] ?? null;

        if ($next) {
            $this->update_transaction($transaction, $current, $next);
        }
    }
    // عدل معلومات المعاملة وحالتها عند كل مسار 
    private function update_transaction(Transaction $transaction, $current, $next): void
    {
        Transaction::where('id', $transaction->id)->update([
            'from' => $current,
            'to' => $next,
            'sent_at' => now(),
            'status_from' => TransactionStatus::COMPLETED->value,
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
