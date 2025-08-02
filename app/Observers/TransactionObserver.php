<?php

namespace App\Observers;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Enums\TransactionStatus;
use App\Models\ArchiveTransaction;
use App\Models\Employee;
use App\Services\UserRoleService;
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

        $newStatus = $transaction->status_to;

        // إذا كانت الحالة مرفوضة
        if ($newStatus === TransactionStatus::REJECTED) {
            Transaction::where('id', $transaction->id)->update([
                'from' => $transaction->to,
                'to' => null,
                'status_from' => TransactionStatus::REJECTED->value,
                'status_to' => null,
                'sent_at' => now(),
            ]);
            $this->archiveOrUpdate($transaction);
            return;
        }

        // إذا كانت الحالة محولة
        if ($newStatus === TransactionStatus::FORWARDED) {
            $this->moveToNextStep($transaction);
            $this->archiveOrUpdate($transaction);
            return;
        }

        // // إذا الحالة منجزة
        // if ($newStatus === TransactionStatus::COMPLETED) {
        //     Transaction::where('id', $transaction->id)->update([
        //         'from' => $transaction->to,
        //         'to' => null,
        //         'sent_at' => now(),
        //     ]);
        //     $this->archiveOrUpdate($transaction);
        //     // حذف المحتوى فقط في حالة منجزة
        //     if ($transaction->content) {
        //         $transaction->content->delete();
        //     }
        //     return;

        // }
    }

    /**
     * جبلي المسار التالي للمعاملة
     */
    private function moveToNextStep(Transaction $transaction): void
    {
        $current = $transaction->to;
        $form = $transaction->content->form;

        $steps = $form->paths()->pluck('path_id')->toArray();
        $index = array_search($current, $steps);

        $next = $steps[$index + 1] ?? null;

        if ($next) {
            $this->updateTransaction($transaction, $current, $next);
        } else {
            $this->archiveOrUpdate($transaction); // هذا بيحفظ الحالة الحالية قبل ما نغيّرها
            // لا يوجد مسار تالي => تعيين حالة المنجزة
            $transaction->update([
                'status_from' => TransactionStatus::FORWARDED->value,
                'status_to' => TransactionStatus::COMPLETED->value,
                'from' => $transaction->to,
                'to'         => null,
                'sent_at'    => now(),
            ]);
            // حذف المحتوى فقط في حالة منجزة
            if ($transaction->content) {
                $transaction->content->delete();
            }
            return;
        }
    }

    /**
     * تحديث معلومات المعاملة ومسارها
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
     * حفظ أو تحديث سجل الأرشيف حسب حالة المعاملة
     */
    private function archiveOrUpdate(Transaction $transaction): void
    {
        $userRoleService = app(UserRoleService::class);
        // تحديد من قام بالتغيير
        $changedBy = null;
        if ($userRoleService->isEmployee()) {
            // إذا كان موظف، نخزّن معرفه من جدول employees
            $changedBy = Employee::where('user_id', Auth::id())->value('id');
        } else {
            // إذا كان مدير أو نائب مدير، نخزّن user_id مباشرة
            $changedBy = Auth::id();
        }
        $changeData = [
            'from_path_id' => $transaction->from,
            'to_path_id' => $transaction->to,
            'status' => $transaction->status_to,
            'changed_at' => now(),
            'changed_by' => $changedBy,
        ];

        $archived = ArchiveTransaction::where('uuid', $transaction->uuid)->first();

        $isCompleted = $archived && $transaction->status_to === TransactionStatus::COMPLETED;

        if (!$archived) {
            $data = [
                'uuid' => $transaction->uuid,
                'receipt_number' => $transaction->receipt_number,
                'status_history' => [$changeData],
                'transaction_content' => $this->TransactionContent($transaction),
            ];

            ArchiveTransaction::create($data);
        } else {
            $history = $archived->status_history;
            $history[] = $changeData;

            $updateData = [
                'status_history' => $history,
            ];

            // إذا الحالة الحالية نهائية يتم تحديث وقت التعديل
            if ($this->isFinalStatus($transaction->status_to->value)) {
                $updateData['updated_at'] = now();
            }
            // إذا اكتملت
            if ($isCompleted) {
                $updateData['final_status'] = TransactionStatus::COMPLETED;
            }

            $archived->update($updateData);
        }
    }




    /**
     * هل الحالة نهائية (مرفوضة أو منجزة) اذا غير هيك بتكون نل
     */
    private function isFinalStatus(?string $status): bool
    {
        return in_array($status, [
            TransactionStatus::REJECTED->value,
            TransactionStatus::COMPLETED->value,
        ]);
    }

    /**
     * تجهيز محتوى المعاملة للاحتفاظ به في الأرشيف
     */
    private function TransactionContent(Transaction $transaction): array
    {
        $content = $transaction->content->loadMissing([
            'form.elements',
            'elementValues.formElement',
            'media',
            'doctor.user',
        ]);

        return [
            'form_id' => $content->form->id,
            'form_name' => $content->form->name,
            'form_cost' => $content->form->cost,
            'doctor_id' => $content->doctor->id ?? null,
            'doctor_name' => $content->doctor->user->name ?? '',
            'doctor_phone' => $content->doctor->user->phone ?? '',
            'doctor_image' => $content->doctor->user->avatar ?? null,
            'payment_status' => $transaction->payment_status ?? null,
            'elements' => $content->elementValues->map(fn($ev) => [
                'label' => $ev->formElement->label,
                'type' => $ev->formElement->type,
                'value' => $ev->value,
            ])->values()->all(),
            'media' => $content->media->map(fn($m) => [
                'file' => $m->file,
                'image' => $m->image,
                'receipt' => $m->receipt,
            ])->values()->all(),
        ];
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
