<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionMovement extends Model
{
    protected $fillable = [
        'transaction_id',
        'from_path_id',
        'to_path_id',
        'status',
        'changed_by',
        'changed_at',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function fromPath()
    {
        return $this->belongsTo(Path::class, 'from_path_id');
    }

    public function toPath()
    {
        return $this->belongsTo(Path::class, 'to_path_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
