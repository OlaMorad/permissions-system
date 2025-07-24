<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Http\Requests\ReceiptStatusRequest;
use App\Http\Requests\TransactionStatusRequest;
use App\Http\Resources\successResource;
use App\Models\ArchiveTransaction;
use App\Services\DoctorTransactionService;
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
        protected UserRoleService $userRoleService,
        protected DoctorTransactionService $doctorService
    ) {}
    // المعاملات الواردة
    public function Import_Transaction()
    {
        if ($this->userRoleService->isFinancial()) {
            $transactions = $this->transactionService->import_for_financial();
        } else {
            $transactions = $this->transactionService->import_transactions();
        }

        return new successResource($transactions);
    }
    // المعاملات الصادرة
    public function Export_Transaction()
    {
        $transactions = $this->transactionService->export_transactions();
        return new successResource($transactions);
    }
    // محتوى المعاملة الواردة
    public function showFormContent($uuid)
    {
        $data = $this->transactionService->getFormContent($uuid);

        return new successResource($data);
    }
    // محتوى المعاملة الصادرة
    public function ShowTransactionContent($uuid)
    {
        return $this->transactionService->show_transaction_content($uuid);
    }
    // ارشيف المعاملات الصادرة
    public function archivedExportedTransactions()
    {
        $data = $this->transactionService->archiveExportedTransactions();
        return new successResource($data);
    }
    // ارشيف المعاملات لكل دائرة
    public function archiveByPath($pathId)
    {
        $data = $this->transactionService->getArchiveTransactionsByPath((int)$pathId);
        return new successResource($data);
    }
    // حجز المعاملة بحالة قيد الدراسة
    public function MarkAsUnderReview(string $uuid)
    {
        return $this->transactionStatusService->markAsUnderReview($uuid);
    }
    // تغيير حالة المعاملة لمحولة او مرفوضة
    public function updateTransactionStatus(TransactionStatusRequest $request, string $uuid)
    {
        return $this->transactionStatusService->updateTransactionStatus($uuid, $request->status());
    }
    // التحقق من وصل الدفع و تغيير حالته
    public function updateReceiptStatus(ReceiptStatusRequest $request)
    {
        return $this->transactionStatusService->updateReceiptStatus($request);
    }
    // الارشيف الكلي
    public function show_archive()
    {
        return new successResource(ArchiveTransaction::all());
    }
    //عرض معاملات الطبيب الحالية
    public function show_doctor_transaction()
    {
        $transactions = $this->doctorService->getCurrentTransactionsForDoctor();
        return new successResource($transactions);
    }
    // عرض معاملات الطبيب المنتهية
    public function archivedDoctorTransactions()
    {
        $transactions = $this->doctorService->getArchivedTransactionsForDoctor();
        return new successResource($transactions);
    }
    // عرض تفاصيل المعاملة الخاصة بالطبيب
    public function showDoctorTransactionDetails(string $uuid)
    {
        $details = $this->doctorService->getTransactionDetailsByUuid($uuid);
        return new successResource($details);
    }
}
