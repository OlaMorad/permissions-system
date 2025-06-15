<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormPath extends Model
{
     protected $table = 'form_path'; 

    protected $fillable = [
        'form_id',
        'path_id',
    ];
}
