<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\FormMedia;
use App\Models\Transaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class PaymentService
{
    public function handlePayment(string $uuid, UploadedFile $receiptFile): void
    {
        DB::transaction(function () use ($uuid, $receiptFile) {
            $transaction = Transaction::where('uuid', $uuid)->firstOrFail();
            // جلب محتوى الفورم المرتبط
            $formContent = $transaction->content;
            // تحقق من أن المستخدم الحالي هو صاحب المعاملة
            $currentDoctorId = Auth::user()->doctor->id ?? null;

            if ($currentDoctorId !== $formContent->doctor_id) {
                // لو مختلف، ارمي استثناء صلاحية مع رسالة
                throw new AuthorizationException('هذه المعاملة ليست لك.');
            }
            // . حفظ الإيصال في storage
            $storedPath = $receiptFile->store('receipts', 'public');

            // . حفظ السطر في جدول form_media
            FormMedia::create([
                'form_content_id' => $formContent->id,
                'receipt' => $storedPath,
            ]);

            // . جلب أول مسار للنموذج
            $firstPathId = DB::table('form_path')
                ->where('form_id', $formContent->form_id)
                ->orderBy('id')
                ->value('path_id');

            // . تحديث بيانات المعاملة
            $transaction->update([
                'payment_status' => PaymentStatus::PAID->value,
                'to' => $firstPathId,
            ]);
        });
    }
}
