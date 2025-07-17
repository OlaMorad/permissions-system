<?php

namespace App\Models;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;

class Manager extends Model
{
      use HasPermissions;
      protected $fillable=[
        'user_id',
        'role_id'
      ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
    
}
