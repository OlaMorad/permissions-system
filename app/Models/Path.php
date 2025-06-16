<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Path extends Model
{
    protected $fillable=['name'];
    public function forms()
    {
        return $this->belongsToMany(Form::class,'form_path');
    }
}
