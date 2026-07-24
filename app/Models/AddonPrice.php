<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddonPrice extends Model
{
    protected $fillable = ['addon_id', 'currency_id', 'price'];

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
