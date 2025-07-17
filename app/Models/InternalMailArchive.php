<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalMailArchive extends Model
{
    protected $fillable=[
        'uuid',
        'from_user_id',
        'subject',
        'status',
        'to',
        'to_phones',
        'received_at'
    ];

    protected $casts = [
    'to' => 'array',
    'to_phones' => 'array',
];

public function fromUser()
{
    return $this->belongsTo(\App\Models\User::class, 'id');
}

}
