<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ExamQuestion extends Pivot
{
    protected $fillable=[
        'exam_id',
        'question_bank_id'
    ];
}
