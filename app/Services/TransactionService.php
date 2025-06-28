<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Http\Resources\successResource;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

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

    public function isFinancial(): bool
    {
        $roleName = Auth::user()->getRoleNames()->first();

        return in_array($roleName, ['موظف المالية', 'رئيس المالية']);
    }
    private function isSectionHead(string $roleName): bool
    {
        return str_starts_with($roleName, 'رئيس');
    }

    // عرض المعاملة و هي معباية
    public function getFormContent(string $transactionUuid): array
    {
        $user = Auth::user();
        $userRole = $user->getRoleNames()->first();
        $pathId = $this->getUserPathId();

        $transaction = Transaction::with([
            'content.form.elements',
            'content.elementValues.formElement',
            'content.media'
        ])->where('uuid', $transactionUuid)->firstOrFail();

        //  إذا الطبيب، تأكد إنو المعاملة إله
        if ($userRole === 'الطبيب') {
            if ($transaction->content->doctor_id !== $user->doctor->id) {
                abort(403, 'لا تملك صلاحية عرض هذه المعاملة.');
            }
        }

        //  إذا موظف القسم ومو رئيس، غير الحالة لقيد الدراسة
        if ($transaction->to === $pathId && !$this->isSectionHead($userRole) && $userRole !== 'الطبيب') {
            $transaction->update([
                'status_to' => TransactionStatus::UNDER_REVIEW->value,
            ]);
        }

        $content = $transaction->content;

        return [
            'form_name' => $content->form->name,
            'elements' => $content->elementValues->map(fn($ev) => [
                'label' => $ev->formElement->label,
                'value' => $ev->value,
            ])->values(),
            'media' => $content->media->map(fn($m) => [
                'file' => $m->file ? asset('storage/' . $m->file) : null,
                'image' => $m->image ? asset('storage/' . $m->image) : null,
                'receipt' => asset('storage/' . $m->receipt),
            ])->values(),
        ];
    }

    public function import_for_financial()
    {
        $pathId = $this->getUserPathId();
        $role = Auth::user()->getRoleNames()->first();

        $query = Transaction::where('to', $pathId)
            ->whereNull('from');

        // إذا المستخدم ليس رئيس القسم، لا تعرض "قيد الدراسة"
        if (!str_starts_with($role, 'رئيس')) {
            $query->where('status_to', '!=', TransactionStatus::UNDER_REVIEW->value);
        }

        $transactions = $query->with(['content.form', 'content.doctor.user'])->get();

        return $transactions->map(function ($transaction) {
            return [
                'uuid' => $transaction->uuid,
                'doctor_name' => $transaction->content->doctor->user->name ?? '',
                'receipt_number' => $transaction->receipt_number,
                'form_name' => $transaction->content->form->name,
                'form_cost' => $transaction->content->form->cost,
                'submitted_at' => $transaction->created_at,
                'received_at' => $transaction->created_at,
            ];
        })->values();
    }

    public function export_for_financial()
    {
        $pathId = $this->getUserPathId();

        $transactions = Transaction::where('from', $pathId)
            ->with(['content.form', 'content.doctor.user'])
            ->get();

        return $transactions->map(function ($transaction) {
            return [
                'uuid' => $transaction->uuid,
                'doctor_name' => $transaction->content->doctor->user->name ?? '',
                'receipt_number' => $transaction->receipt_number,
                'form_name' => $transaction->content->form->name,
                'form_cost' => $transaction->content->form->cost,
                'status' => $transaction->status_from,
                'submitted_at' => $transaction->created_at,
                'sent_at' => $transaction->sent_at,
            ];
        })->values();
    }

    public function import_transactions()
    {
        $userPathId = $this->getUserPathId();
        $role = Auth::user()->getRoleNames()->first();

        $query = Transaction::where('to', $userPathId);

        // إذا الموظف مو رئيس القسم، خبي المعاملات قيد الدراسة
        if (!str_starts_with($role, 'رئيس')) {
            $query->where('status_to', '!=', TransactionStatus::UNDER_REVIEW->value);
        }

        $transactions = $query
            ->with(['content.form:id,name', 'content.doctor.user:id,name', 'toPath', 'fromPath'])
            ->get();

        return $this->mapImport($transactions);
    }

    public function export_transaction()
    {
        $userPathId = $this->getUserPathId();

        $transactions = Transaction::where('from', $userPathId)
            ->with(['content.form:id,name', 'content.doctor.user:id,name', 'toPath', 'fromPath'])
            ->get();

        return $this->mapExport($transactions);
    }

    private function mapImport($transactions)
    {
        return $transactions->map(function ($transaction) {
            return [
                'uuid' => $transaction->uuid,
                'doctor_image' => $transaction->content->doctor->user->avatar ?? null,
                'doctor_name' => $transaction->content->doctor->user->name ?? '',
                'doctor_phone' => $transaction->content->doctor->user->phone ?? '',
                'form_name' => $transaction->content->form->name ?? '',
                'from_path' => optional($transaction->fromPath)->name ?? null,
                'submitted_at' => $transaction->created_at,
                'received_at' => $transaction->received_at,
            ];
        })->values();
    }

    private function mapExport($transactions)
    {
        return $transactions->map(function ($transaction) {
            return [
                'uuid' => $transaction->uuid,
                'doctor_image' => $transaction->content->doctor->user->avatar ?? null,
                'doctor_name' => $transaction->content->doctor->user->name ?? '',
                'doctor_phone' => $transaction->content->doctor->user->phone ?? '',
                'form_name' => $transaction->content->form->name ?? '',
                'to_path' => optional($transaction->toPath)->name ?? null,
                'status' => $transaction->status_from,
                'submitted_at' => $transaction->created_at,
                'sent_at' => $transaction->sent_at,
            ];
        })->values();
    }
}
