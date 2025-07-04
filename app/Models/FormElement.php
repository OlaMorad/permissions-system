<?php

namespace App\Models;

use App\Enums\Element_Type as EnumsElement_Type;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class FormElement extends Model
{
use HasFactory; 
    protected $fillable = ['form_id', 'label', 'type'];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'type' => EnumsElement_Type::class,  //  ربط الحقل مع enum
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
