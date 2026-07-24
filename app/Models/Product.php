<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\{HasMedia, InteractsWithMedia};
use Spatie\Translatable\HasTranslations;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Product extends Model implements HasMedia, Auditable
{
    use InteractsWithMedia, HasTranslations,AuditableTrait;
    protected $fillable = [
        'name', 'description', 'price', 'is_active', 'type', 'category_id', 'slug', 'is_box', 'active', 'product_type', 'ingrediant_text', 'is_pickup', 'is_trending', 'is_new_plates', 'is_home', 'order', 'show_in_limit_offer'
    ];
    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'ingrediant_text' => 'array',
        'is_pickup' => 'boolean',
        'is_trending' => 'boolean',
        'is_new_plates' => 'boolean',
        'is_home' => 'boolean',
        'show_in_limit_offer' => 'boolean',
    ];
    public $translatable = ['name', 'description', 'ingrediant_text'];

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function addonGroups()
    {
        return $this->belongsToMany(AddonGroup::class, 'addon_group_product')
            ->withPivot(['max_quantity', 'is_required'])
            ->where('addon_groups.active', 1)
            ->withTimestamps();
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'product_branches', 'product_id', 'branch_id')
            ->withPivot('status');
    }

    public function box()
    {
        return $this->belongsTo(Box::class, 'box_id');
    }

    public function properties()
    {
        return $this->belongsToMany(ProductDefinition::class, 'product_properties', 'product_id', 'product_definition_id')
            ->withPivot('value');
    }

    public function definitions()
    {
        return $this->belongsToMany(ProductDefinition::class, 'product_properties', 'product_id', 'product_definition_id')
            ->withPivot('value')->where('key', '!=', 'calories');
    }

    public function definitions_calories()
    {
        return $this->belongsToMany(ProductDefinition::class, 'product_properties', 'product_id', 'product_definition_id')
            ->withPivot('value')->where('key', 'calories');
    }


    public function addons()
    {
        return $this->belongsToMany(Addon::class, 'addon_group_products', 'product_id', 'addon_id')
            ->withPivot(['type', 'order', 'addon_group_id', 'box_id'])
            ->whereNull('box_id')
            ->where('addons.active', 1)
            ->orderBy('addon_group_products.order', 'asc')
            ->orderBy('addon_group_products.type', 'asc');
    }

    public function boxAddons()
    {
        return $this->belongsToMany(Addon::class, 'addon_group_products', 'product_id', 'addon_id')
            ->withPivot(['type', 'order', 'addon_group_id', 'box_id'])
            ->whereNotNull('box_id')
            ->where('addons.active', 1)
            ->orderBy('addon_group_products.order', 'asc')
            ->orderBy('addon_group_products.type', 'asc');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'box_products', 'box_id', 'product_id')
            ->withPivot(['title', 'is_required', 'max_count', 'min_count', 'order'])
            ->orderBy('box_products.order', 'asc')
            ->withTimestamps();
    }

    /**
     * Scope to filter products by city
     * If city is selected, show only products from branches in that city with available status
     * If no city is selected, show all products
     */
    public function scopeByCity($query, $cityId = null)
    {
        if ($cityId) {
            return $query->whereHas('branches', function($q) use ($cityId) {
                // Only check if branch has the city in branch_cities table
                // This ensures products only show for branches explicitly assigned to the city
                // Also check that product status is available in that branch
                $q->where('branches.active', true)
                  ->where('product_branches.status', 'available')
                  ->whereHas('cities', function($cityQuery) use ($cityId) {
                      $cityQuery->where('locations.id', $cityId);
                  });
            });
        }
        // If no city selected, return all products (no filtering)
        return $query;
    }

    /**
     * Scope to filter products by branch and status
     * Show only products that are available in the specified branch
     */
    public function scopeAvailableInBranch($query, $branchId)
    {
        return $query->whereHas('branches', function($q) use ($branchId) {
            $q->where('branches.id', $branchId)
              ->where('branches.active', true)
              ->where('product_branches.status', 'available');
        });
    }

    public function box_products()
    {
        return $this->hasMany(BoxProduct::class, 'box_ic');
    }

    public function offers()
    {
        return $this->belongsToMany(Offer::class, 'offer_products');
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_products')
            ->withPivot('order', 'show_price', 'category_id')
            ->orderBy('menu_products.order')
            ->withTimestamps();
    }

    public function notes()
    {
        return $this->hasMany(ProductNote::class);
    }

    public function menuProducts()
    {
        return $this->hasMany(MenuProduct::class);
    }

//    helper functions

public function getCaloriesAttribute()
{
    if ($this->relationLoaded('definitions_calories')) {
        $definition = $this->definitions_calories->firstWhere('key', 'calories');
    } else {
        $definition = $this->definitions_calories()->where('key', 'calories')->first();
    }

    $value = $definition?->pivot?->value ?? null;
    return $value !== null ? (int) round($value) : null;
}

public static function formatNutritionValue($value)
{
    if ($value === null) {
        return '';
    }
    return rtrim(rtrim(number_format((float)$value, 2, '.', ''), '0'), '.');
}

    public function priceInCurrency($currencyId)
    {
        return $this->prices()->where('currency_id', $currencyId)->first();
    }
    public function getCurrentPrice($currencyKey)
    {
        $currencyId = Currency::where('key', $currencyKey)->value('id') ?? Currency::value('id');
        $price = $this->prices()->where('currency_id', $currencyId)->first();
        if (! $price) {
            return null;
        }

        $offer = $this->offers()->active()->first();
        if ($offer) {
            if ($offer->discount_type === 'percentage') {
                return $price->price - ($price->price * ($offer->discount_value / 100));
            } else {
                return $price->price - $offer->discount_value;
            }
        }

        if ($price->has_discount) {
            return $price->discount_price;
        }

        return $price->price;
    }

    /**
     * Get price details (original and discounted) for a currency
     * Returns array with 'original', 'discounted', 'has_discount'
     */
    public function getPriceDetails($currencyKey)
    {
        $currencyId = Currency::where('key', $currencyKey)->value('id') ?? Currency::value('id');
        $price = $this->prices()->where('currency_id', $currencyId)->first();

        if (! $price) {
            return [
                'original' => null,
                'discounted' => null,
                'has_discount' => false,
                'has_offer' => false
            ];
        }

        $originalPrice = (float) $price->price;
        $discountedPrice = $originalPrice;
        $hasDiscount = false;
        $hasOffer = false;

        // Check for active offer first
        $offer = $this->offers()->active()->first();
        if ($offer && $offer->discount_value > 0) {
            $hasOffer = true;
            $discountValue = (float) $offer->discount_value;
            
            // Calculate discounted price based on discount type
            if ($offer->discount_type === 'percentage') {
                $discountedPrice = $originalPrice - ($originalPrice * ($discountValue / 100));
            } else {
                // Fixed discount: subtract the discount value from original price
                $discountedPrice = $originalPrice - $discountValue;
            }
            
            // Ensure discounted price is not negative
            if ($discountedPrice < 0) {
                $discountedPrice = 0;
            }
            
            // Round to 2 decimal places
            $discountedPrice = round($discountedPrice, 2);
            
            // Only set has_discount if there's actually a discount (discounted price is less than original)
            if ($discountedPrice < $originalPrice && $discountedPrice > 0) {
                $hasDiscount = true;
            } else {
                // If calculation resulted in same or higher price, reset to original
                $discountedPrice = $originalPrice;
                $hasDiscount = false;
                $hasOffer = false;
            }
        } elseif ($price->has_discount && $price->discount_price < $price->price) {
            // Use product price discount if no active offer
            $hasDiscount = true;
            $discountedPrice = (float) $price->discount_price;
        }

        // Final validation: ensure has_discount is only true if prices are actually different
        if ($hasDiscount && abs($originalPrice - $discountedPrice) < 0.01) {
            $hasDiscount = false;
        }

        return [
            'original' => round($originalPrice, 2),
            'discounted' => round($discountedPrice, 2),
            'has_discount' => $hasDiscount,
            'has_offer' => $hasOffer
        ];
    }


    public function getAvailabilityStatus()
    {
        $type = $this->type ?? false;

        if ($type) {
            return $type;
        } else {
            return 'none';
        }
    }


}
