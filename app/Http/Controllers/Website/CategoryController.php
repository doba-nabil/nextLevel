<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Favourite;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function categories(Request $request, $slug = null)
    {
        $type = $request->get('type', 'product');
        $is_box = $type === 'box' ? 1 : 0;
        $menuType = session('menu_type', 'delivery');
        
        // Get selected city from session
        $userLocation = session('user_location');
        $cityId = $userLocation['city_id'] ?? null;

        // include is_box, menuType, and cityId in cache key
        $cacheKey = "categories_" . $menuType . "_" . $is_box . "_city_" . $cityId;

        $categories = Cache::rememberForever($cacheKey, function () use ($menuType, $is_box, $cityId) {
            // If viewing products, show categories that have either products OR boxes
            // If viewing boxes, show only categories with boxes
            if ($is_box == 0) {
                // Viewing products - show categories with products OR boxes
                // Use whereHas with orWhereHas to explicitly check for both products and boxes
                return Category::where('active', 1)
                    ->where(function ($query) use ($menuType, $cityId) {
                        // Categories that have products (is_box = 0)
                        $query->whereHas('products', function ($q) use ($menuType, $cityId) {
                            $q->where('is_box', 0)
                                ->where('active', true)
                                ->where(function ($subQ) use ($menuType) {
                                    $subQ->where('type', $menuType)
                                        ->orWhere('type', 'both');
                                })
                                ->byCity($cityId);
                        })
                        // OR categories that have boxes (is_box = 1)
                        ->orWhereHas('products', function ($q) use ($menuType, $cityId) {
                            $q->where('is_box', 1)
                                ->where('active', true)
                                ->where(function ($subQ) use ($menuType) {
                                    $subQ->where('type', $menuType)
                                        ->orWhere('type', 'both');
                                })
                                ->byCity($cityId);
                        });
                    })
                    ->withCount(['products as products_count' => function ($query) use ($menuType, $cityId) {
                        $query->where('is_box', 0)
                            ->where('active', true)
                            ->where(function ($q) use ($menuType) {
                                $q->where('type', $menuType)
                                    ->orWhere('type', 'both');
                            })
                            ->byCity($cityId);
                    }])
                    ->withCount(['products as boxes_count' => function ($query) use ($menuType, $cityId) {
                        $query->where('is_box', 1)
                            ->where('active', true)
                            ->where(function ($q) use ($menuType) {
                                $q->where('type', $menuType)
                                    ->orWhere('type', 'both');
                            })
                            ->byCity($cityId);
                    }])
                    ->with(['products' => function ($query) use ($menuType, $cityId) {
                        // Load both products (is_box = 0) and boxes (is_box = 1)
                        $query->where('active', true)
                            ->where(function ($q) use ($menuType) {
                                $q->where('type', $menuType)
                                    ->orWhere('type', 'both');
                            })
                            ->byCity($cityId)
                            ->with(['addonGroups', 'addons'])
                            ->orderBy('is_box', 'asc') // Show products first, then boxes
                            ->orderBy('order', 'asc')
                            ->orderBy('id', 'asc');
                    }])
                    ->orderBy('order', 'asc')
                    ->orderBy('id', 'asc')
                    ->get(['id', 'name', 'slug'])
                    ->map(function ($category) {
                        $category->total_count = ($category->products_count ?? 0) + ($category->boxes_count ?? 0);
                        $category->image_url = $category->getFirstMediaUrl('categories');
                        return $category;
                    })
                    ->filter(function ($category) {
                        return ($category->products_count > 0) || ($category->boxes_count > 0);
                    })
                    ->values();
            } else {
                // Viewing boxes - show only categories with boxes
                return Category::where('active', 1)
                    ->whereHas('products', function ($query) use ($menuType, $cityId) {
                        $query->where('is_box', 1)
                            ->where('active', true)
                            ->where(function ($q) use ($menuType) {
                                $q->where('type', $menuType)
                                    ->orWhere('type', 'both');
                            })
                            ->byCity($cityId);
                    })
                    ->withCount(['products' => function ($query) use ($menuType, $cityId) {
                        $query->where('is_box', 1)
                            ->where('active', true)
                            ->where(function ($q) use ($menuType) {
                                $q->where('type', $menuType)
                                    ->orWhere('type', 'both');
                            })
                            ->byCity($cityId);
                    }])
                    ->with(['products' => function ($query) use ($menuType, $cityId) {
                        $query->where('is_box', 1)
                            ->where('active', true)
                            ->where(function ($q) use ($menuType) {
                                $q->where('type', $menuType)
                                    ->orWhere('type', 'both');
                            })
                            ->byCity($cityId)
                            ->with(['addonGroups', 'addons']);
                    }])
                    ->orderBy('order', 'asc')
                    ->orderBy('id', 'asc')
                    ->get(['id', 'name', 'slug'])
                    ->map(function ($category) {
                        $category->total_count = $category->products_count ?? 0;
                        $category->image_url = $category->getFirstMediaUrl('categories');
                        return $category;
                    })
                    ->values();
            }
        });

        $specialCategories = collect();
        
        $boxesProducts = Product::where('active', true)
            ->where('is_box', true)
            ->where(function ($q) use ($menuType) {
                $q->where('type', $menuType)->orWhere('type', 'both');
            })
            ->byCity($cityId)
            ->with(['addonGroups', 'addons'])
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        
        if ($boxesProducts->count() > 0) {
            $boxesCategory = (object) [
                'id' => 'boxes',
                'name' => __('website.boxes'),
                'slug' => 'boxes',
                'image_url' => asset('website/assets/img/box.PNG'),
                'products' => $boxesProducts,
                'products_count' => $boxesProducts->count(),
                'boxes_count' => 0,
                'total_count' => $boxesProducts->count(),
                'is_special' => true
            ];
            $specialCategories->push($boxesCategory);
        }
        
        $offersProducts = Product::where('active', true)
            ->where('show_in_limit_offer', true)
            ->where(function ($q) use ($menuType) {
                $q->where('type', $menuType)->orWhere('type', 'both');
            })
            ->byCity($cityId)
            ->with(['addonGroups', 'addons'])
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        
        if ($offersProducts->count() > 0) {
            $offersCategory = (object) [
                'id' => 'offers',
                'name' => __('website.offers'),
                'slug' => 'offers',
                'image_url' => asset('website/assets/img/offers.jpeg'),
                'products' => $offersProducts,
                'products_count' => $offersProducts->count(),
                'boxes_count' => 0,
                'total_count' => $offersProducts->count(),
                'is_special' => true
            ];
            $specialCategories->push($offersCategory);
        }
        
        $trendingProducts = Product::where('active', true)
            ->where('is_trending', true)
            ->where(function ($q) use ($menuType) {
                $q->where('type', $menuType)->orWhere('type', 'both');
            })
            ->byCity($cityId)
            ->with(['addonGroups', 'addons'])
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        
        if ($trendingProducts->count() > 0) {
            $trendingCategory = (object) [
                'id' => 'trending',
                'name' => __('website.trending'),
                'slug' => 'trending',
                'image_url' => asset('website/assets/img/trending.jpeg'),
                'products' => $trendingProducts,
                'products_count' => $trendingProducts->count(),
                'boxes_count' => 0,
                'total_count' => $trendingProducts->count(),
                'is_special' => true
            ];
            $specialCategories->push($trendingCategory);
        }
        
        $newProducts = Product::where('active', true)
            ->where('is_new_plates', true)
            ->where(function ($q) use ($menuType) {
                $q->where('type', $menuType)->orWhere('type', 'both');
            })
            ->byCity($cityId)
            ->with(['addonGroups', 'addons'])
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        
        if ($newProducts->count() > 0) {
            $newCategory = (object) [
                'id' => 'new',
                'name' => __('website.new'),
                'slug' => 'new',
                'image_url' => asset('website/assets/img/new.jpeg'),
                'products' => $newProducts,
                'products_count' => $newProducts->count(),
                'boxes_count' => 0,
                'total_count' => $newProducts->count(),
                'is_special' => true
            ];
            $specialCategories->push($newCategory);
        }
        
        $allCategories = $specialCategories->merge($categories);
        $categories = $allCategories;

        $currentCategory = $slug
            ? $categories->firstWhere('slug', $slug)
            : $categories->first();

        if ($is_box == 0 && $currentCategory) {
            if (isset($currentCategory->is_special) && $currentCategory->is_special) {
                $products = $currentCategory->products ?? collect();
            } else {
                $products = Product::where('category_id', $currentCategory->id)
                    ->where('active', true)
                    ->where(function ($q) use ($menuType) {
                        $q->where('type', $menuType)
                            ->orWhere('type', 'both');
                    })
                    ->byCity($cityId)
                    ->with(['addonGroups', 'addons'])
                    ->orderBy('is_box', 'asc') // Show products first, then boxes
                    ->orderBy('order', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();
            }
        } else {
            $products = $currentCategory?->products ?? collect();
        }

        // Get cart products with quantities
        $cart = session('cart', []);
        $cartProductIds = collect($cart)->pluck('product_id', 'product_id')->toArray();
        $cartQuantities = collect($cart)->mapWithKeys(function ($item) {
            return [$item['product_id'] => $item['quantity']];
        })->toArray();

        // Eager load addon relationships for products if it's a collection of models
        if ($products instanceof \Illuminate\Database\Eloquent\Collection) {
            $products->loadMissing(['addonGroups', 'addons']);
        }

        // Get favorite product IDs for authenticated users
        $favoriteProductIds = collect();
        if (auth('web')->check()) {
            $favoriteProductIds = Favourite::where('user_id', auth('web')->id())
                ->pluck('product_id');
        }

        // Get logo URL from database
        $settingModel = Setting::getSettingModel();
        $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');

        return view('website.categories.categories', compact('categories', 'products', 'currentCategory', 'type', 'cartProductIds', 'cartQuantities', 'favoriteProductIds', 'logoUrl'));
    }

}
