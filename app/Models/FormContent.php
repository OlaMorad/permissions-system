<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormContent extends Model
{
    protected $fillable = ['form_id', 'user_id'];

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
}
