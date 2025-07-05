<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchiveTransaction extends Model
{
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

}
