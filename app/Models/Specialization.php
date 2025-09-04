<?php

namespace App\Models;

use App\Enums\ExamRequestEnum;
use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    protected $fillable = ['name', 'bachelors_degree', 'experience_years', 'status'];

    protected $casts = [
        'experience_years' => 'array',
        'status' => ExamRequestEnum::class,
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function QuestionBank(){
        return $this->hasMany(QuestionBank::class);
    }

       public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_specialization');
    }
}
