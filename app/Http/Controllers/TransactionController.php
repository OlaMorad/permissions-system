<?php

namespace App\Http\Controllers;

use App\Http\Resources\successResource;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{

    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }


    public function Import_Transaction()
    {
        $transactions = $this->transactionService->import_transactions();
        return new successResource($transactions);
    }
    public function Export_Transaction()
    {
        $transactions = $this->transactionService->export_transaction();
        return new successResource($transactions);
    }
    public function showFormContent($id)
    {
        $data = $this->transactionService->getFormContent($id);

        return new successResource($data);
    }
    public function Update_Status_to_Complete($id)
    {
        return $this->transactionService->completeTransaction($id);
    }
}
