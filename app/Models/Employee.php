<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class Employee extends Model
{

    protected $fillable=[
      'user_id',
      'role_id',
      'manager_id'

    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }
}
