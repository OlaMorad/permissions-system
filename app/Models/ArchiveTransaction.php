<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class ArchiveTransaction extends Model
{
    use Searchable;
    protected $table = 'archive_transactions';

    protected $fillable = [
        'uuid',
        'receipt_number',
        'transaction_content',
        'status_history',
        'final_status',
        'updated_at'
    ];

    protected $casts = [
        'transaction_content' => 'array',
        'status_history' => 'array',
    ];

    public function toSearchableArray()
    {
        $transactionContent = $this->transaction_content ?? [];

        return [
            'uuid' => $this->uuid,
            'receipt_number' => $this->receipt_number,
            'doctor_name' => $transactionContent['doctor_name'] ?? null,
        ];
    }
    public function searchableAs()
    {
        return 'archive_transactions';
    }
}
