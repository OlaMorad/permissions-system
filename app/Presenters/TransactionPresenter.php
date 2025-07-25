<?php

namespace App\Presenters;

use App\Enums\TransactionStatus;
use App\Models\Path;
use Illuminate\Support\Collection;
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

    public static function forImportList(Collection $transactions): array
    {
        return $transactions->map(fn($t) => self::forImport($t))->values()->toArray();
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

    public static function FinanceImportList(Collection $transactions): array
    {
        return $transactions->map(fn($t) => self::FinanceImport($t))->values()->toArray();
    }

    public static function ArchiveFinanceExport($transaction, $pathId): array
    {
        $content = $transaction->transaction_content ?? [];
        return [
            'uuid' => $transaction->uuid,
            'doctor_name' => $content['doctor_name'] ?? '',
            'receipt_number' => $transaction->receipt_number,
            'form_name' => $content['form_name'] ?? '',
            'form_cost' => $content['form_cost'] ?? null,
            'status' => self::extractStatusFromHistory($transaction->status_history, $pathId),
            'to_path' => self::getNextPath($transaction->status_history, $pathId, $content),
            'submitted_at' => $transaction->created_at,
            'sent_at' => $transaction->updated_at,
        ];
    }

    public static function ArchiveForExport($transaction, $pathId): array
    {
        $content = $transaction->transaction_content ?? [];
        return [
            'uuid' => $transaction->uuid,
            'doctor_image' => $content['doctor_image'] ?? null,
            'doctor_name' => $content['doctor_name'] ?? '',
            'doctor_phone' => $content['doctor_phone'] ?? '',
            'form_name' => $content['form_name'] ?? '',
            'status' => self::extractStatusFromHistory($transaction->status_history, $pathId),
            'to_path' => self::getNextPath($transaction->status_history, $pathId, $content),
            'submitted_at' => $transaction->created_at,
            'sent_at' => $transaction->updated_at,
        ];
    }

    public static function exportList(Collection $transactions, int $pathId, bool $isFinance): array
    {
        return $transactions->map(function ($t) use ($pathId, $isFinance) {
            return $isFinance
                ? self::ArchiveFinanceExport($t, $pathId)
                : self::ArchiveForExport($t, $pathId);
        })->values()->toArray();
    }

    public static function formatElements($elementValues): array
    {
        return $elementValues->map(fn($ev) => [
            'label' => $ev->formElement->label,
            'type' => $ev->formElement->type->value,
            'value' => $ev->value,
        ])->values()->toArray();
    }

    public static function formatArchivedElements(array $elements): array
    {
        return array_map(fn($e) => [
            'label' => $e['label'] ?? '',
            'value' => $e['value'] ?? '',
            'type' => $e['type'] ?? '',
        ], $elements);
    }

    public static function formatMedia(Collection $media): array
    {
        return $media->map(function ($m) {
            return [
                'file' => !empty($m['file']) ? asset('storage/' . $m['file']) : null,
                'image' => !empty($m['image']) ? asset('storage/' . $m['image']) : null,
                'receipt' => !empty($m['receipt']) ? asset('storage/' . $m['receipt']) : null,
            ];
        })->toArray();
    }
    public static function formatArchivedMedia(array $media): array
    {
        return array_map(function ($m) {
            return [
                'file' => !empty($m['file']) ? asset('storage/' . $m['file']) : null,
                'image' => !empty($m['image']) ? asset('storage/' . $m['image']) : null,
                'receipt' => !empty($m['receipt']) ? asset('storage/' . $m['receipt']) : null,
            ];
        }, $media);
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
        $status = self::extractStatusFromHistory($history, $pathId);
        if ($status === TransactionStatus::REJECTED->value) return '--';

        $formId = $transactionContent['form_id'] ?? null;
        if (!$formId) return '--';

        $allPathIds = DB::table('form_path')->where('form_id', $formId)->orderBy('id')->pluck('path_id')->toArray();
        $index = array_search($pathId, $allPathIds);
        $nextPathId = $allPathIds[$index + 1] ?? null;
        return $nextPathId ? (Path::find($nextPathId)?->name ?? '--') : '--';
    }
}
