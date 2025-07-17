<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamRequest extends Model
{
    protected $fillable = [
        'uuid',
        'doctor_id',
        'status',
        'form_content_id'
    ];


    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function formContent()
{
    return $this->belongsTo(FormContent::class);
}

}
