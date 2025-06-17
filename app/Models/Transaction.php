<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'form_content_id',
        'from',
        'to',
        'sent_at',
        'received_at',
        'status_from',
        'status_to'
    ];

    protected $casts = [
        'status_from' => TransactionStatus::class,
        'status_to' => TransactionStatus::class,
    ];

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
