<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormMedia extends Model
{
    protected $fillable = [
    'form_content_id',
    'file_path',
    'image_path',
];
    public function content()
    {
        return $this->belongsTo(FormContent::class);
    }
}
