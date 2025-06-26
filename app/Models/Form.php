<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\FormStatus;

class Form extends Model
{
    protected $fillable = ['name', 'status', 'cost'];

    protected $casts = [
        'status' => FormStatus::class,
    ];

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
