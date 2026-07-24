<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'usage_limit', 'expire_at', 'user_id', 'min_order_price', 'active'
    ];

    protected $casts = [
        'expire_at' => 'datetime',
        'value' => 'decimal:3',
        'min_order_price' => 'decimal:3',
        'usage_limit' => 'integer',
        'active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isValidForUser($userId)
    {
        if(!$this->isValid()) return false;
        if($this->user_id && $this->user_id != $userId) return false;
        return true;
    }

    public function apply($amount, $userId = null)
    {
        if(!$this->isValidForUser($userId)) {
            throw new \Exception('Coupon is invalid or not for this user');
        }
        if($this->type === 'percent') {
            return $amount * ((100 - $this->value) / 100);
        }
        return max(0, $amount - $this->value);
    }

    public function decreaseUsage()
    {
        if($this->usage_limit !== null) {
            $this->decrement('usage_limit');
        }
    }

    protected function isValid()
    {
        if(!$this->active) return false;
        if($this->expire_at && now()->gt($this->expire_at)) return false;
        if($this->usage_limit !== null && $this->usage_limit <= 0) return false;
        return true;
    }

}
