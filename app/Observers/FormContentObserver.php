<?php

namespace App\Observers;

use App\Models\ExamRequest;
use App\Models\FormContent;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Enums\TransactionStatus;
use App\Enums\StatusInternalMail;
use Illuminate\Support\Facades\DB;

class FormContentObserver
{
    /**
     * Handle the FormContent "created" event.
     */
    public function created(FormContent $formContent)
    {
        $form = $formContent->form;
        if ($form->cost == 0) {
            $examRequest = ExamRequest::create([
                'uuid' =>  Str::uuid()->toString(),
                'doctor_id' => $formContent->doctor_id,
               'form_content_id' => $formContent->id,
                'status' => StatusInternalMail::PENDING->value,
            ]);
        }
         else {
                $prefix = now()->format('ymd');
                do {
                    $receiptNumber = $prefix . random_int(10000, 99999);
                    $exists = DB::table('transactions')->where('receipt_number', $receiptNumber)->exists();
                } while ($exists);

                Transaction::create([
                    'form_content_id' => $formContent->id,
                    'from' => null,
                    'to' => null,
                    'status_from' => TransactionStatus::FORWARDED,
                    'status_to' => TransactionStatus::PENDING,
                    'received_at' => now(),
                    'receipt_number' => $receiptNumber,
                    'changed_by' => null,
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
