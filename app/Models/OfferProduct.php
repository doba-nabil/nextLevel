<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferProduct extends Model
{
    protected $fillable = ['offer_id', 'product_id'];

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
