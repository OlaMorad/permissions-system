<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function path()
    {
        return $this->belongsTo(Path::class);
    }
}
