<?php

namespace App\Services;

use App\Models\ArchiveTransaction;
use Illuminate\Support\Facades\Auth;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DoctorTransactionService
{
    // جلب المعاملات الحالية التي لم تنهي مسارها بعد او ترفض
    public function getCurrentTransactionsForDoctor()
    {
        $doctorId = Auth::user()->doctor->id;

        // اجلب قائمة UUIDs من المعاملات المؤرشفة المنتهية
        $archived = ArchiveTransaction::whereNotNull('final_status')->pluck('uuid')->toArray();

        return Transaction::with('content.form')
            ->whereHas('content', function ($query) use ($doctorId) {
                $query->where('doctor_id', $doctorId);
            })
            // استبعاد المعاملات الموجودة في الأرشيف والتي انتهت
            ->whereNotIn('uuid', $archived)
            ->orderBy('created_at')
            ->get()
            ->map(function ($transaction) {
                return [
                    'uuid' => $transaction->uuid,
                    'name' => $transaction->content->form->name ?? null,
                    'created_at' => $transaction->created_at->format('Y-m-d'),
                ];
            });
    }

    // المعاملات المنتهية (موجودة في جدول archive_transactions)
    public function getArchivedTransactionsForDoctor()
    {
        $doctorId = Auth::user()->doctor->id;

        return ArchiveTransaction::query()
            ->whereIn('final_status', [
                TransactionStatus::COMPLETED->value,
                TransactionStatus::REJECTED->value,
            ])
            ->whereJsonContains('transaction_content->doctor_id', $doctorId)
            ->orderBy('created_at')
            ->get()
            ->map(function ($archive) {
                $content = $archive->transaction_content;

                return [
                    'uuid' => $archive->uuid,
                    'name' => $content['form_name'] ?? null,
                    'created_at' => $archive->created_at->format('Y-m-d'),
                ];
            });
    }
    public function getTransactionDetailsByUuid(string $uuid): ?array
    {
        // أولاً: نحاول نجيب المعاملة من جدول transactions
        $transaction = Transaction::with(['content.form', 'content.doctor.user', 'fromPath', 'toPath'])
            ->where('uuid', $uuid)
            ->first();

        if ($transaction) {
            $form = $transaction->content->form;
            $steps = $form->paths()->pluck('path_id')->toArray();
            $stepNames = $form->paths()->select('paths.id', 'paths.name')->pluck('name', 'id')->toArray();

            return [
                'uuid' => $transaction->uuid,
                'cost ' => $form->cost ?? null,
                'full_path' => collect($steps)->map(fn($id) => $stepNames[$id] ?? '')->values()->all(),
                'current_path' => in_array($transaction->status_to, [TransactionStatus::COMPLETED, TransactionStatus::REJECTED])
                    ? 'انتهت'
                    : ($transaction->toPath->name ?? null),
                'doctor_name' => $transaction->content->doctor->user->name ?? '',
                'created_at' => $transaction->created_at->format('Y-m-d'),
                'status_to' => $transaction->status_to,
                'payment_status' => $transaction->payment_status,
                'receipt_number' => $transaction->receipt_number,
                'ended_at' => in_array($transaction->status_to, [TransactionStatus::COMPLETED, TransactionStatus::REJECTED])
                    ? $transaction->updated_at->format('Y-m-d')
                    : null,

            ];
        }

        // ثانياً: إذا المعاملة مو موجودة بجدول المعاملات، نحاول نجيبها من الأرشيف
        $archived = ArchiveTransaction::where('uuid', $uuid)->first();

        if ($archived) {
            $content = $archived->transaction_content;
            $statusHistory = collect($archived->status_history);
            $stepIds = $statusHistory->pluck('from_path_id')->merge($statusHistory->pluck('to_path_id'))->unique()->filter();

            // من الأفضل جلب أسماء المسارات مباشرة من جدول paths
            $pathNames = DB::table('paths')->whereIn('id', $stepIds)->pluck('name', 'id');

            return [
                'uuid' => $archived->uuid,
                'cost' => $content['form_cost'] ?? null,
                'full_path' => $stepIds->map(fn($id) => $pathNames[$id] ?? '')->values()->all(),
                'current_path' => 'انتهت',
                'doctor_name' => $content['doctor_name'] ?? '',
                'created_at' => $archived->created_at->format('Y-m-d'),
                'status_to' => $archived->final_status ?? null,
                'payment_status' => $content['payment_status'] ?? null,
                'receipt_number' => $archived->receipt_number,
                'ended_at' => $archived->final_status ? $archived->updated_at->format('Y-m-d') : null,

            ];
        }
        // إذا ما لقينا المعاملة
        return null;
    }
}
