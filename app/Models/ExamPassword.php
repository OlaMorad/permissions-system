<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamPassword extends Model
{
    protected $fillable = ['password'];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
