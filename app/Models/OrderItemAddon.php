<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemAddon extends Model
{
    protected $fillable = ['order_item_id', 'addon_id', 'name', 'price'];

    public function item()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }
}
