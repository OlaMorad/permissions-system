<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Enums\TransactionStatus;
use Carbon\Carbon;

class ArchiveRejectedTransactions extends Command
{
    protected $signature = 'transactions:archive-rejected';
    protected $description = 'تحديث حالة المعاملات المرفوضة بعد مرور 48 ساعة من الرفض وحذف محتواها';

    public function handle()
    {

$transactions = Transaction::with('archive', 'content')
    ->join('archive_transactions', 'archive_transactions.uuid', '=', 'transactions.uuid')
    ->where('transactions.status_from', TransactionStatus::REJECTED)
    ->select('transactions.*', 'archive_transactions.final_status', 'archive_transactions.status_history')
    ->get()
    ->filter(function ($transaction) {
        return is_null($transaction->final_status);
    });

foreach ($transactions as $transaction) {
$history = json_decode($transaction->status_history, true); // الآن أصبحت Array
$lastChangeEntry = collect($history)->last();
$lastChange = $lastChangeEntry['changed_at'] ?? null;

// dd( $lastChange);
    if ($lastChange && Carbon::parse($lastChange)->diffInHours(now()) >= 48) {
        $transaction->archive->update([
            'final_status' => TransactionStatus::REJECTED,
        ]);

        if ($transaction->content) {
            $transaction->content->delete();
        }

        $this->info("تم تحديث المعاملة UUID: {$transaction->uuid}");
    }

            }
        }
    }

