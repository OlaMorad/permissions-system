<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    protected $fillable = ['name', 'bachelors_degree', 'experience_years'];

    protected $casts = [
        'experience_years' => 'array',
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
}
