<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{ 
    protected $fillable = ['user_id', 'address_id', 'order_number', 'status', 'total', 'delivery_cost', 'meal_type', 'scheduled_date', 'scheduled_time', 'lat', 'long', 'order_type', 'branch_id', 'guest_name', 'guest_phone', 'guest_email', 'guest_address', 'order_notes', 'coupon_id', 'coupon_code', 'discount_amount', 'payment_method', 'wallet_amount', 'gateway_amount', 'payment_status', 'payment_id', 'payment_response', 'armada_id', 'armada_header', 'armada_link', 'armada_qr', 'delivery_status', 'delivery_info'];

    protected $casts = [
        'delivery_info' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    protected static function booted()
    {
        static::creating(function ($order) {
            $order->order_number = 'ORD-' . strtoupper(uniqid());
        });
    }

    public function deductStock(): void
    {
        app(\App\Services\BranchStockService::class)->deductForOrder($this);
    }
}

