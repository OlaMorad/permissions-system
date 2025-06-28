<?php

namespace App\Http\Controllers;

use App\Http\Resources\successResource;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use App\Services\TransactionStatusService;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService,
        protected TransactionStatusService $transactionStatusService
    ) {}


    public function Import_Transaction()
    {
        if ($this->transactionService->isFinancial()) {
            $transactions = $this->transactionService->import_for_financial();
        } else {
            $transactions = $this->transactionService->import_transactions();
        }

        return new successResource($transactions);
    }

    public function Export_Transaction()
    {
        if ($this->transactionService->isFinancial()) {
            $transactions = $this->transactionService->export_for_financial();
        } else {
            $transactions = $this->transactionService->export_transaction();
        }

        return new successResource($transactions);
    }

    public function showFormContent($id)
    {
        $data = $this->transactionService->getFormContent($id);

        return new successResource($data);
    }
    public function forwardTransaction($id)
    {
        return $this->transactionStatusService->forward_transaction($id);
    }
    public function rejectTransaction(string $uuid)
    {
        return $this->transactionStatusService->reject_transaction($uuid);
    }

    public function approveReceipt($uuid)
    {
        $data = $this->transactionStatusService->approve_receipt($uuid);

        return new successResource($data);
    }
    public function rejectReceipt(string $uuid)
    {
        return $this->transactionStatusService->reject_receipt($uuid);
    }
}
