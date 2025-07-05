<?php

namespace App\Presenters;

use App\Models\Path;

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

    public static function forExport($transaction): array
    {
        return [
            'uuid' => $transaction->uuid,
            'doctor_image' => $transaction->content->doctor->user->avatar ?? null,
            'doctor_name' => $transaction->content->doctor->user->name ?? '',
            'doctor_phone' => $transaction->content->doctor->user->phone ?? '',
            'form_name' => $transaction->content->form->name ?? '',
            'to_path' => $transaction->toPath->name  ?? '--',
            'status' => $transaction->status_from,
            'submitted_at' => $transaction->created_at,
            'sent_at' => $transaction->sent_at,
        ];
    }

    public static function FinanceExport($transaction): array
    {
        return [
            'uuid' => $transaction->uuid,
            'doctor_name' => $transaction->content->doctor->user->name ?? '',
            'receipt_number' => $transaction->receipt_number,
            'form_name' => $transaction->content->form->name,
            'form_cost' => $transaction->content->form->cost,
            'status' => $transaction->status_from,
            'to_path' => $transaction->toPath->name  ?? '--',
            'submitted_at' => $transaction->created_at,
            'sent_at' => $transaction->sent_at,
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
        $path = self::getNextPath($transaction->status_history, $pathId);

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
        $path = self::getNextPath($transaction->status_history, $pathId);


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
    private static function getNextPath(array $history, int $pathId): string
    {
        foreach ($history as $entry) {
            if (isset($entry['from_path_id'], $entry['to_path_id']) && (int)$entry['from_path_id'] === $pathId) {
                $toPath = Path::find($entry['to_path_id']);
                return $toPath?->name ?? '--';
            }
        }
        return '--';
    }
}
