<?php

namespace App\Presenters;

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
            'to_path' => optional($transaction->toPath)->name  ?? null,
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
}
