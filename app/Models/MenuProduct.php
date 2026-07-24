<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class MenuProduct extends Model implements Auditable
{
    use AuditableTrait;

    protected $table = 'menu_products';

    protected $fillable = [
        'menu_id',
        'product_id',
        'category_id',
        'order',
        'show_price'
    ];

    protected $casts = [
        'order' => 'integer',
        'show_price' => 'boolean',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}


