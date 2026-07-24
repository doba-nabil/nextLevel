<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\{HasMedia, InteractsWithMedia};
use Spatie\Translatable\HasTranslations;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Menu extends Model implements HasMedia, Auditable
{
    use InteractsWithMedia, HasTranslations, AuditableTrait;

    protected $fillable = [
        'name',
        'content',
        'slug',
        'is_active'
    ];

    protected $casts = [
        'name' => 'array',
        'content' => 'array',
    ];

    public $translatable = ['name', 'content'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'menu_products')
            ->withPivot('order', 'show_price', 'category_id')
            ->orderBy('menu_products.order')
            ->withTimestamps();
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'menu_products', 'menu_id', 'category_id')
            ->whereNotNull('menu_products.category_id')
            ->distinct();
    }

    public function menuProducts()
    {
        return $this->hasMany(MenuProduct::class);
    }
}

