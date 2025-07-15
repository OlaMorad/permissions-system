<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamRequest extends Model
{
    protected $fillable =[
      'uuid',
      'doctor_id',
      'status'

    ];


    public function doctor(){
        return $this->belongsTo(Doctor::class);
    }



}
