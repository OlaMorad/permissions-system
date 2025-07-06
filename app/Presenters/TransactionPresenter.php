<?php

namespace App\Presenters;

use App\Enums\TransactionStatus;
use App\Models\Path;
use Illuminate\Support\Facades\DB;

class TransactionPresenter
{
    public static function forImport($transaction): array
    {
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
    }

    public static function FinanceImport($transaction): array
    {
        return [
            'uuid' => $transaction->uuid,
            'doctor_name' => $transaction->content->doctor->user->name ?? '',
            'receipt_number' => $transaction->receipt_number,
            'form_name' => $transaction->content->form->name,
            'form_cost' => $transaction->content->form->cost,
            'submitted_at' => $transaction->created_at,
            'received_at' => $transaction->created_at,
        ];
    }
    public static function ArchiveFinanceExport($transaction, $pathId): array
    {
        $content = $transaction->transaction_content ?? [];
        $status = self::extractStatusFromHistory($transaction->status_history, $pathId);
        $path = self::getNextPath($transaction->status_history, $pathId, $transaction->transaction_content);

        return [
            'uuid' => $transaction->uuid,
            'doctor_name' => $content['doctor_name'] ?? '',
            'receipt_number' => $transaction->receipt_number,
            'form_name' => $content['form_name'] ?? '',
            'form_cost' => $content['form_cost'] ?? null,
            'status' =>  $status,
            'to_path' => $path,
            'submitted_at' => $transaction->created_at,
            'sent_at' => $transaction->updated_at,
        ];
    }
    public static function ArchiveForExport($transaction, $pathId): array
    {
        $content = $transaction->transaction_content ?? [];
        $status = self::extractStatusFromHistory($transaction->status_history, $pathId);
        $path = self::getNextPath($transaction->status_history, $pathId, $transaction->transaction_content);

        return [
            'uuid' => $transaction->uuid,
            'doctor_image' => $content['doctor_image'] ?? null,
            'doctor_name' => $content['doctor_name'] ?? '',
            'doctor_phone' => $content['doctor_phone'] ?? '',
            'form_name' => $content['form_name'] ?? '',
            'to_path' => $path,
            'status' =>  $status,
            'submitted_at' => $transaction->created_at,
            'sent_at' => $transaction->updated_at,
        ];
    }
    private static function extractStatusFromHistory(array $history, int $pathId): string
    {
        foreach ($history as $entry) {
            if (isset($entry['to_path_id'], $entry['status']) && (int)$entry['to_path_id'] === $pathId) {
                return $entry['status'];
            }
        }
        return '--';
    }
    private static function getNextPath(array $history, int $pathId, array $transactionContent): string
    {
        // أولاً نستخرج الحالة الفعلية للمسار الحالي
        $status = self::extractStatusFromHistory($history, $pathId);

        // إذا كانت الحالة مرفوضة، ما في مسار تالي
        if ($status === TransactionStatus::REJECTED->value) {
            return '--';
        }
        // اذا كانت محولة جبلي المسار التالي
        $formId = $transactionContent['form_id'] ?? null;
        if (!$formId) return '--';

        $allPathIds = DB::table('form_path')->where('form_id', $formId)->orderBy('id')
            ->pluck('path_id')->toArray();

        $index = array_search($pathId, $allPathIds);
        if ($index === false || !isset($allPathIds[$index + 1])) {
            return '--';
        }
        $nextPathId = $allPathIds[$index + 1];
        $path = Path::find($nextPathId);
        return $path?->name ?? '--';
    }
}
