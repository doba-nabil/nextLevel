<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    protected $fillable = ['product_id', 'currency_id', 'price', 'discount_price', 'discount_percentage', 'has_discount' , 'discount_type'];

    protected static function booted()
    {
        static::saving(function ($model) {
            $price = (float) $model->price;
            $discountPrice = $model->discount_price ? (float) $model->discount_price : null;
            
            // If discount_price is set and valid
            if ($discountPrice !== null && $price > 0 && $discountPrice < $price && $discountPrice > 0) {
                $model->discount_percentage = round((($price - $discountPrice) / $price) * 100, 2);
                $model->discount_type = $model->discount_type && $model->discount_type !== 'none' ? $model->discount_type : 'fixed';
                $model->has_discount = true;
            } elseif ($model->discount_percentage && $price > 0) {
                // If discount_percentage is set, calculate discount_price
                $calculatedDiscountPrice = round($price - ($price * $model->discount_percentage / 100), 2);
                if ($calculatedDiscountPrice < $price && $calculatedDiscountPrice > 0) {
                    $model->discount_price = $calculatedDiscountPrice;
                    $model->discount_type = 'percentage';
                    $model->has_discount = true;
            } else {
                // Invalid discount, reset to price
                $model->discount_price = $price;
                $model->discount_percentage = null;
                $model->discount_type = null;
                $model->has_discount = false;
            }
        } else {
            // No valid discount, set discount_price to price
            $model->discount_price = $price;
            $model->discount_percentage = null;
            $model->discount_type = null;
            $model->has_discount = false;
        }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function setDiscountPriceAttribute($value)
    {
        $this->attributes['discount_price'] = $value;

        if (!is_null($value) && $this->price > 0) {
            $this->attributes['discount_percentage'] =
                round((($this->price - $value) / $this->price) * 100, 2);
        }
    }

    public function setDiscountPercentageAttribute($value)
    {
        $this->attributes['discount_percentage'] = $value;

        if (!is_null($value) && $this->price > 0) {
            $this->attributes['discount_price'] =
                round($this->price - ($this->price * $value / 100), 2);
        }
    }

    public function getFinalPriceAttribute()
    {
        return $this->discount_price ?? $this->price;
    }
}
