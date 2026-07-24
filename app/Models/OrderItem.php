<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'quantity', 'price', 'total', 'parent_item_id', 'meta', 'notes'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function addons()
    {
        return $this->hasMany(OrderItemAddon::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(OrderItem::class, 'parent_item_id');
    }

    public function children()
    {
        return $this->hasMany(OrderItem::class, 'parent_item_id');
    }
}

