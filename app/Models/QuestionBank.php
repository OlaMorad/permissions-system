<?php

namespace App\Models;

use App\Enums\OptionEnum;
use App\Enums\DifficultyLevelEnum;
use Illuminate\Database\Eloquent\Model;

class QuestionBank extends Model
{
    protected $fillable=[
    'specialization_id',
    'question',
    'option_a',
    'option_b',
    'option_c',
    'option_d',
    'correct_answer',
    'difficulty_level'
    ];

        protected $casts = [
        'difficulty_level' => DifficultyLevelEnum::class,
    ];

    public function specialization(){
        return $this->belongsTo(Specialization::class);
    }
}
