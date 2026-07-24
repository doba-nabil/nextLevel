<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Addon extends Model implements Auditable
{
    use HasTranslations, AuditableTrait;
    protected $fillable = ['addon_group_id', 'name', 'price' , 'active'];
    public $translatable = ['name'];

    public function group()
    {
        return $this->belongsTo(AddonGroup::class, 'addon_group_id');
    }

    public function prices()
    {
        return $this->hasMany(AddonPrice::class);
    }

    public function getCurrentPrice($currencyKey)
    {
        $currencyId = Currency::where('key', $currencyKey)->value('id') ?? Currency::value('id');
        $price = $this->prices()->where('currency_id', $currencyId)->first();
        if (! $price) {
            return null;
        }
        return $price->price;
    }
}
