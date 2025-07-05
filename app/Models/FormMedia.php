<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class FormMedia extends Model
{
    use HasFactory;
    protected $fillable = [
        'form_content_id',
        'file',
        'image',
        'receipt'
    ];
    public function content()
    {
        return $this->belongsTo(FormContent::class);
    }
}
