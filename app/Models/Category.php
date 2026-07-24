<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\{HasMedia, InteractsWithMedia};
use Spatie\Translatable\HasTranslations;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Category extends Model implements HasMedia, Auditable
{
    use InteractsWithMedia, HasTranslations, AuditableTrait;
    protected $fillable = [
        'name','slug', 'active', 'order'
    ];
    public $translatable = ['name'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function menuProducts()
    {
        return $this->hasMany(MenuProduct::class);
    }

}
