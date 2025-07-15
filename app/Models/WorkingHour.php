<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingHour extends Model
{
    protected $fillable = ['start_time', 'end_time', 'day_off'];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
