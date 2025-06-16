<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable=[
        'doctor_id',
        'form_content_id',
        'mail_status'
    ];
    public function content()
    {
        return $this->belongsTo(FormContent::class);
    }

        public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
