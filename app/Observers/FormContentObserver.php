<?php

namespace App\Observers;

use App\Enums\TransactionStatus;
use App\Models\FormContent;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class FormContentObserver
{
    /**
     * Handle the FormContent "created" event.
     */
    public function created(FormContent $formContent)
    {
        $firstPathId = DB::table('form_path')
            ->where('form_id', $formContent->form_id)
            ->orderBy('id')
            ->value('path_id');

        if ($firstPathId) {
            // نحسب رقم الإيصال التالي
            $lastReceiptNumber = DB::table('transactions')->max('receipt_number');
            $nextReceiptNumber = $lastReceiptNumber ? ((int)$lastReceiptNumber + 1) : 1;

            // تنسيق الرقم ليكون 6 خانات مثل: 000001
            $formattedReceiptNumber = str_pad($nextReceiptNumber, 6, '0', STR_PAD_LEFT);
            Transaction::create([
                'form_content_id' => $formContent->id,
                'from' => null,
                'to' => $firstPathId,
                'status_from' => TransactionStatus::PENDING,
                'status_to' => TransactionStatus::PENDING,
                'received_at' => now(),
                'receipt_number' => $formattedReceiptNumber,

            ]);
        }
    }


    /**
     * Handle the FormContent "updated" event.
     */
    public function updated(FormContent $formContent): void
    {
        //
    }

    /**
     * Handle the FormContent "deleted" event.
     */
    public function deleted(FormContent $formContent): void
    {
        //
    }

    /**
     * Handle the FormContent "restored" event.
     */
    public function restored(FormContent $formContent): void
    {
        //
    }

    /**
     * Handle the FormContent "force deleted" event.
     */
    public function forceDeleted(FormContent $formContent): void
    {
        //
    }
}
