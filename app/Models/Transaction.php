<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    protected $fillable = [
        'uuid',
        'form_content_id',
        'from',
        'to',
        'sent_at',
        'received_at',
        'status_from',
        'status_to',
        'receipt_number',
        'receipt_status'
    ];

    protected $casts = [
        'status_from' => TransactionStatus::class,
        'status_to' => TransactionStatus::class,
    ];

    protected static function booted()
    {
        static::creating(function ($transaction) {
            $transaction->uuid = (string) Str::uuid();
        });
    }

    public function content()
    {
        return $this->belongsTo(FormContent::class,'form_content_id');
    }

    public function fromPath()
    {
        return $this->belongsTo(Path::class,'from');
    }


    public function toPath()
    {
        return $this->belongsTo(Path::class,'to');
    }
}
