<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class AddonGroup extends Model implements Auditable
{
    use HasTranslations, AuditableTrait;
    public $translatable = ['name'];
    protected $fillable = ['name', 'max_items', 'type', 'active', 'is_selection_mandatory', 'max_selections', 'min_selections'];

    public function addons()
    {
        return $this->hasMany(Addon::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'addon_group_products')
            ->withPivot(['max_quantity', 'is_required'])
            ->withTimestamps();
    }
}
