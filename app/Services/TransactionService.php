<?php

namespace App\Services;

use App\Http\Resources\successResource;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use App\Enums\TransactionStatus;

class TransactionService
{
    // جلب path_id للمستخدم الحالي
    private function getUserPathId(): ?int
    {
        $user = Auth::user();
        $roleName = $user->getRoleNames()->first();
        $role = Role::where('name', $roleName)->first();

        return $role?->path_id;
    }

    // تحديث حالة المعاملة لمكتملة
    public function completeTransaction($id)
    {
        $pathId = $this->getUserPathId();
        $transaction = Transaction::where('to', $pathId)->findOrFail($id);

        $transaction->status_to = TransactionStatus::COMPLETED->value;
        $transaction->save();

        return new successResource(['message' => 'تم تحديث حالة المعاملة إلى مكتملة']);
    }

    // جلب محتوى النموذج المرتبط بالمعاملة
    public function getFormContent(int $transactionId)
    {
        $transaction = Transaction::with([
            'content.form.elements',
            'content.elementValues.formElement',
            'content.media',
            'content.doctor.user'
        ])->findOrFail($transactionId);

        $content = $transaction->content;

        return [
            'form_name' => $content->form->name,
            'doctor_name' => $content->doctor->user->name,
            'elements' => $content->elementValues->map(fn($ev) => [
                'label' => $ev->formElement->label,
                'value' => $ev->value,
            ])->values(),
            'media' => $content->media->map(fn($m) => [
                'file' => $m->file_path,
                'image' => $m->image_path,
            ])->values(),
        ];
    }

    // استيراد المعاملات المرتبطة بمسار المستخدم (to)
    public function import_transactions()
    {
        $userPathId = $this->getUserPathId();

        $transactions = Transaction::where('to', $userPathId)
            ->with(['content.form:id,name', 'content.doctor.user:id,name', 'toPath', 'fromPath'])
            ->get();

        return $this->mapImport($transactions);
    }

    // تصدير المعاملات المرتبطة بمسار المستخدم (from)
    public function export_transaction()
    {
        $userPathId = $this->getUserPathId();

        $transactions = Transaction::where('from', $userPathId)
            ->with(['content.form:id,name', 'content.doctor.user:id,name', 'toPath', 'fromPath'])
            ->get();

        return $this->mapExport($transactions);
    }

    // تابع ماب الاستيراد
    private function mapImport($transactions)
    {
        return $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'form_name' => $transaction->content->form->name ?? '',
                'doctor_name' => $transaction->content->doctor->user->name ?? '',
                'from_path' => optional($transaction->fromPath)->name ?? null,
                'to_path' => $transaction->toPath->name,
                'received_at' => $transaction->received_at,
                'created_at' => $transaction->created_at,
            ];
        })->values();
    }

    // تابع ماب التصدير
    private function mapExport($transactions)
    {
        return $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'form_name' => $transaction->content->form->name ?? '',
                'doctor_name' => $transaction->content->doctor->user->name ?? '',
                'from_path' => optional($transaction->fromPath)->name ?? null,
                'to_path' => $transaction->toPath->name,
                'sent_at' => $transaction->sent_at,
                'created_at' => $transaction->created_at,
            ];
        })->values();
    }
}
