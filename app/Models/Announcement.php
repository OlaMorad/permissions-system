<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = ['title', 'body'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
    ];
    protected $hidden = [
        'updated_at',
    ];
}
