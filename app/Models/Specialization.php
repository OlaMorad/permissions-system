<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    protected $fillable = ['name', 'bachelors_degree', 'experience_years'];

    protected $casts = [
        'experience_years' => 'array',
    ];

    public function QuestionBank(){
        return $this->hasMany(QuestionBank::class);
    }
}
