<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Doctor extends Model
{
    protected $fillable=['user_id'];
       public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

     public function formContent()
    {
        return $this->hasOne(FormContent::class);
    }

      public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
