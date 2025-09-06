<?php

namespace App\Models;

use App\Enums\Program_ExamStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use Laravel\Scout\Searchable;

class Exam extends Model
{
    use Searchable;
    protected $fillable = [
        'specialization_id',
        'program_id',
        'day',
        'date',
        'status',
        'simple_ratio',
        'average_ratio',
        'hard_ratio',
        'start_time',
        'end_time',
        'candidates_count',
        'present_candidates_count',
        'success_rate',
    ];

    protected $casts = [
        'status' => Program_ExamStatus::class,
        //'date' => 'date',
    ];
    protected $appends = ['exam_time'];

    protected function examTime(): Attribute
    {
        return Attribute::make(
            get: fn() =>  Carbon::parse($this->end_time)->format('H:i') . ' - ' .
                Carbon::parse($this->start_time)->format('H:i')
        );
    }
    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }

    public function questions()
    {
        return $this->belongsToMany(QuestionBank::class, 'exam_questions', 'exam_id', 'question_bank_id')
            ->using(\App\Models\ExamQuestion::class)
            ->withPivot('id') // للحصول على id من جدول exam_questions
            ->withTimestamps();
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'day' => $this->day,
            'date' => $this->date,
            'status' => $this->status,
            'exam_time'=>$this->exam_time,
            'specialization_name' => $this->specialization?->name, // البحث حسب اسم الاختصاص
        ];
    }

    public function password()
    {
        return $this->hasOne(ExamPassword::class);
    }
}
