<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\FormStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Form extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'status', 'cost'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
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
