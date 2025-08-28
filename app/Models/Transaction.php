<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Transaction extends Model
{
    use HasFactory,Searchable;
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
        'receipt_status',
        'payment_status',
        'changed_by'
    ];

    protected $casts = [
        'status_from' => TransactionStatus::class,
        'status_to' => TransactionStatus::class,
        'payment_status' => PaymentStatus::class,

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

    public function archive()
{
    return $this->hasOne(ArchiveTransaction::class, 'uuid', 'uuid');
}


    public function toSearchableArray()
    {
        // نجيب محتوى المعاملة مع الطبيب
        $content = $this->content?->loadMissing(['doctor.user']);

        return [
            'uuid' => $this->uuid,
            'receipt_number' => $this->receipt_number,
            'doctor_name' => $content->doctor->user->name ?? null,
        ];
    }
}
