<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\DoctorExamStatus;
use App\Enums\DegreeRating;

class Candidate extends Model
{

    protected $fillable = [
        'exam_id',
        'doctor_id',
        'status',
        'exam_date',
        'nomination_date',
        'exam_number',
        'degree',
        'rating',
    ];
    protected $casts = [
        'status' => DoctorExamStatus::class,
        'rating' => DegreeRating::class,
    ];
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
