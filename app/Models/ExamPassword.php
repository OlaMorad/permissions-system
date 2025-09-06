<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamPassword extends Model
{
    protected $fillable = ['exam_id', 'password'];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
