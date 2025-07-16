<?php

namespace App\Models;

use App\Enums\ExamRequestEnum;
use App\Enums\Program_ExamStatus;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = [
        'month',
        'year',
        'status',
        'approved',
        'exams_count',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'status' => Program_ExamStatus::class,
        'approved' => ExamRequestEnum::class,
    ];

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
