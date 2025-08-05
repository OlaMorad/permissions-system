<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    protected $fillable = [
        'phone',
        'email',
        'telegram_link',
        'whatsapp_number',
        'facebook_link',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
