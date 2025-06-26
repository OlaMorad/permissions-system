<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Path extends Model
{
    protected $fillable=['name'];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function forms()
    {
        return $this->belongsToMany(Form::class,'form_path');
    }

    // المعاملات التي أُرسلت من هذا المسار
    public function sentTransactions()
    {
        return $this->hasMany(Transaction::class, 'from');
    }

    // المعاملات التي استُقبلت في هذا المسار
    public function receivedTransactions()
    {
        return $this->hasMany(Transaction::class, 'to');
    }
    public function roles()
    {
        return $this->hasMany(Role::class);
    }
}
