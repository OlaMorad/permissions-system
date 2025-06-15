<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormMedia extends Model
{
    public function content()
    {
        return $this->belongsTo(FormContent::class);
    }
}
