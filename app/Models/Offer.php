<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'title', 'discount_type', 'discount_value',
        'start_date', 'end_date', 'is_active'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'discount_value' => 'decimal:2',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'offer_products');
    }

    public function scopeActive($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', $now)
            ->whereDate('end_date', '>=', $now);
    }
}
