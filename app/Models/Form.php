<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $fillable = ['name', 'status'];

    public function elements()
    {
        return $this->hasMany(FormElement::class);
    }
    public function contents()
    {
        return $this->hasMany(FormContent::class);
    }
    public function paths()
    {
        return $this->belongsToMany(Path::class,'form_path');
    }
}
