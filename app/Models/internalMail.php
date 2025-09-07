<?php

namespace App\Models;

use App\Enums\StatusInternalMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InternalMail extends Model
{
    protected $fillable=[
        'from_user_id',
        // 'to_user_id',
        'status',
        'subject',
        'body',
        'uuid',
    ];

       protected $casts = [
        'status' => StatusInternalMail::class,
    ];

    public function fromUser()
{
    return $this->belongsTo(User::class, 'from_user_id');
}




    public function paths()
    {
        return $this->belongsToMany(Path::class, 'internal_mail_paths');
    }

      protected static function booted()
    {
        static::creating(function ($transaction) {
            $transaction->uuid = (string) Str::uuid();
        });
    }
}
