<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    public function content()
    {
        return $this->belongsTo(FormContent::class);
    }

        public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
