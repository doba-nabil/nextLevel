<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\{HasMedia, InteractsWithMedia};
use Spatie\Translatable\HasTranslations;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Currency extends Model implements HasMedia, Auditable
{
    use InteractsWithMedia, HasTranslations, AuditableTrait;

    protected $fillable = [
        'name','sign', 'location_id', 'key', 'rate_per_point', 'points_per_currency', 'minimum_usable_points'
    ];

    public $translatable = ['name'];

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public static function getCurrentCurrencySign()
    {
        $currency = session('currency');
        $currency = self::where('key', $currency)->first() ?? self::first();
        return $currency ? $currency->sign : '';
    }
}
