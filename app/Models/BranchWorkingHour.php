<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchWorkingHour extends Model
{
    protected $fillable = [
        'branch_id',
        'from_day', 'to_day',
        'from_time', 'to_time'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
