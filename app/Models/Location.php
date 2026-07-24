<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Location extends Model implements HasMedia, Auditable
{
    use InteractsWithMedia, HasTranslations, AuditableTrait;

    public $translatable = ['name', 'description'];

    protected $fillable = [
        'name', 'type', 'parent_id','shipping_fee_near', 'shipping_fee_far', 'min_order_near', 'min_order_far', 'active', 'phone_code', 'code', 'delivery_time'
    ];

    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function currency()
    {
        return $this->hasOne(Currency::class, 'location_id');
    }

    public function children()
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function scopeCountries($query)
    {
        return $query->where('type', 'country');
    }

    public function scopeStates($query)
    {
        return $query->where('type', 'state');
    }

    public function scopeCities($query)
    {
        return $query->where('type', 'city');
    }
}
