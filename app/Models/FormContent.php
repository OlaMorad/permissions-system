<?php

namespace App\Models;

use App\Models\ExamRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormContent extends Model
{
    use HasFactory;
    protected $fillable = ['form_id', 'doctor_id'];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
    public function media()
    {
        return $this->hasMany(FormMedia::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function elementValues()
{
    return $this->hasMany(form_element_value::class);
}
public function examRequest()
{
    return $this->hasOne(ExamRequest::class, 'doctor_id', 'doctor_id');
}


}
