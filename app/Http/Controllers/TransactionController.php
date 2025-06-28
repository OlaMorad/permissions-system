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
    public function Update_Status_to_Complete($id)
    {
        return $this->transactionStatusService->completeTransaction($id);
    }

    // الدالة الجديدة لتحديث حالة الإيصال والحالة المعاملة
    public function approveReceipt($uuid)
    {
        $data = $this->transactionStatusService->approve_receipt($uuid);

        return new successResource($data);
    }
}
