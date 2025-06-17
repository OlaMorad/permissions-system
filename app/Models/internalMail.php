<?php

namespace App\Models;

use App\Enums\StatusInternalMail;
use Illuminate\Database\Eloquent\Model;

class internalMail extends Model
{
    protected $fillable=[
        'from_user_id',
        // 'to_user_id',
        'status',
        'subject',
        'body'
    ];

       protected $casts = [
        'status' => StatusInternalMail::class,
    ];

    public function fromUser()
{
    return $this->belongsTo(User::class, 'from_user_id');
}

//    public function toUser()
//     {
//         return $this->belongsTo(User::class, 'to_user_id');
//     }


    public function paths()
    {
        return $this->belongsToMany(Path::class, 'internal_mail_paths');
    }
}
