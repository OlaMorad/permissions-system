<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalMailArchive extends Model
{
    protected $fillable=[
        'uuid',
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

}
