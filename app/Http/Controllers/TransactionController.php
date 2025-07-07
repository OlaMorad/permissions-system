<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Http\Requests\ReceiptStatusRequest;
use App\Http\Requests\TransactionStatusRequest;
use App\Http\Resources\successResource;
use App\Models\ArchiveTransaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use App\Services\TransactionStatusService;
use App\Services\UserRoleService;
use Illuminate\Validation\Rules\Enum;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService,
        protected TransactionStatusService $transactionStatusService,
        protected UserRoleService $userRoleService
    ) {}

    public function Import_Transaction()
    {
        if ($this->userRoleService->isFinancial()) {
            $transactions = $this->transactionService->import_for_financial();
        } else {
            $transactions = $this->transactionService->import_transactions();
        }

        return new successResource($transactions);
    }
    public function Export_Transaction()
    {
        $transactions = $this->transactionService->export_transactions();
        return new successResource($transactions);
    }

    public function showFormContent($uuid)
    {
        $data = $this->transactionService->getFormContent($uuid);

        return new successResource($data);
    }
    public function ShowTransactionContent($uuid)
    {
       return $this->transactionService->show_transaction_content($uuid);
    }
    public function archivedExportedTransactions()
    {
        $data = $this->transactionService->archiveExportedTransactions();
        return new successResource($data);
    }
    public function updateTransactionStatus(TransactionStatusRequest $request, string $uuid)
    {
        return $this->transactionStatusService->updateTransactionStatus($uuid, $request->status());
    }

    public function updateReceiptStatus(ReceiptStatusRequest $request)
    {
        return $this->transactionStatusService->updateReceiptStatus($request);
    }
    public function show_archive()
    {
        return new successResource(ArchiveTransaction::all());
    }
}
