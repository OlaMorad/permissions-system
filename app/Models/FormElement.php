<?php

namespace App\Models;

use App\Enums\Element_Type as EnumsElement_Type;
use Illuminate\Database\Eloquent\Model;

class FormElement extends Model
{
    protected $fillable = ['form_id', 'label', 'type'];

    protected $casts = [
        'type' => EnumsElement_Type::class,  //  ربط الحقل مع enum
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
