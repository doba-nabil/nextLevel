<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ProductDefinition extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name','unit', 'key', 'active'
    ];

    public $translatable = ['name'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_properties', 'product_definition_id', 'product_id')
            ->withPivot('value');
    }
}
