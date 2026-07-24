<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuProduct;
use App\Models\Favourite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MenuController extends Controller
{
    public function menus(Request $request)
    {
        // Optimize: Cache menus query
        $menuType = session('menu_type', 'delivery');
        $cacheKey = "menus_active_{$menuType}";
        $allMenus = Cache::remember($cacheKey, 3600, function () {
            return Menu::where('is_active', true)
                ->whereHas('menuProducts', function($query) {
                    $query->whereNotNull('category_id')
                          ->whereNotNull('product_id')
                          ->whereHas('product', function($q) {
                              $q->where('active', true);
                          })
                          ->whereHas('category', function($c) {
                              $c->where('active', true);
                          });
                })
                ->get(['id', 'name', 'slug']);
        });
        
        if ($allMenus->isEmpty()) {
            return view('website.menus.menus', ['allMenus' => collect(), 'active_menu' => null, 'categories' => collect(), 'active_category' => null]);
        }
        
        $menuSlug = $request->get('menu_slug', $allMenus->first()->slug ?? null);
        if (!$menuSlug) {
            return view('website.menus.menus', ['allMenus' => collect(), 'active_menu' => null, 'categories' => collect(), 'active_category' => null]);
        }
        
        // Get selected city from session
        $userLocation = session('user_location');
        $cityId = $userLocation['city_id'] ?? null;
        
        $active_menu = Menu::where('slug', $menuSlug)
            ->where('is_active', true)
            ->whereHas('menuProducts', function($query) {
                $query->whereNotNull('category_id')
                      ->whereNotNull('product_id')
                      ->whereHas('product', function($q) {
                          $q->where('active', true);
                      })
                      ->whereHas('category', function($c) {
                          $c->where('active', true);
                      });
            })
            ->firstOrFail();
        
        $categorySlug = $request->get('category_slug');
        
        // Optimize: Eager load with city filter
        $active_menu->load(['menuProducts' => function($query) use ($cityId) {
            $query->whereNotNull('category_id')
                  ->whereNotNull('product_id')
                  ->with(['product' => function($q) use ($cityId) {
                      $q->where('active', true)
                        ->byCity($cityId)
                        ->with('category', 'definitions', 'addonGroups', 'addons');
                  }, 'category' => function($c) {
                      $c->where('active', true);
                  }]);
        }]);
        
        // Group menu products by category and filter out categories without products
        $categories = $active_menu->menuProducts
            ->filter(function($mp) {
                return $mp->product && $mp->product->active && $mp->category && $mp->category->active;
            })
            ->groupBy('category_id')
            ->map(function ($menuProducts, $categoryId) {
                $category = $menuProducts->first()->category;
                $products = $menuProducts->sortBy('order')->map(function ($menuProduct) {
                    return [
                        'menu_product' => $menuProduct,
                        'product' => $menuProduct->product,
                        'show_price' => $menuProduct->show_price
                    ];
                })->filter(function($item) {
                    return $item['product'] && $item['product']->active;
                })->values();
                
                return [
                    'slug' => $category->slug,
                    'category' => $category,
                    'products' => $products
                ];
            })
            ->filter(function ($group) {
                return $group['category'] !== null && $group['products']->isNotEmpty();
            })
            ->values();
        
        $active_category = null;
        if ($categories->isNotEmpty()) {
            if ($categorySlug) {
                $active_category = $categories->firstWhere('slug', $categorySlug);
            }
            if (!$active_category) {
                $active_category = $categories->first();
            }
        }

        // Get cart products with quantities
        $cart = session('cart', []);
        $cartProductIds = collect($cart)->pluck('product_id', 'product_id')->toArray();
        $cartQuantities = collect($cart)->mapWithKeys(function ($item) {
return [$item['product_id'] => $item['quantity']];
        })->toArray();

        // Get favorite product IDs for authenticated users
        $favoriteProductIds = collect();
        if (auth('web')->check()) {
            $favoriteProductIds = Favourite::where('user_id', auth('web')->id())
                ->pluck('product_id');
        }

        // Get logo URL from settings
        $settingModel = \App\Models\Setting::getSettingModel();
        $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');

        return view('website.menus.menus', compact('allMenus', 'active_menu', 'categories', 'active_category', 'cartProductIds', 'cartQuantities', 'favoriteProductIds', 'logoUrl'));
    }

    public function menu($slug, Request $request)
    {
        $menu = Menu::where('slug', $slug)
            ->where('is_active', true)
            ->whereHas('menuProducts', function($query) {
                $query->whereNotNull('category_id')
                      ->whereNotNull('product_id')
                      ->whereHas('product', function($q) {
                          $q->where('active', true);
                      })
                      ->whereHas('category', function($c) {
                          $c->where('active', true);
                      });
            })
            ->firstOrFail();
        
        $categorySlug = $request->get('category_slug');
        
        // Get selected city from session
        $userLocation = session('user_location');
        $cityId = $userLocation['city_id'] ?? null;
        
        $menu->load(['menuProducts' => function($query) use ($cityId) {
            $query->whereNotNull('category_id')
                  ->whereNotNull('product_id')
                  ->with(['product' => function($q) use ($cityId) {
                      $q->where('active', true)
                        ->byCity($cityId)
                        ->with('category', 'definitions', 'addonGroups', 'addons');
                  }, 'category' => function($c) {
                      $c->where('active', true);
                  }]);
        }]);
        
        // Group menu products by category and filter out categories without products
        $categories = $menu->menuProducts
            ->filter(function($mp) {
                return $mp->product && $mp->product->active && $mp->category && $mp->category->active;
            })
            ->groupBy('category_id')
            ->map(function ($menuProducts, $categoryId) {
                $category = $menuProducts->first()->category;
                $products = $menuProducts
                    ->sortBy('order')
                    ->map(function ($menuProduct) {
                        return [
                            'menu_product' => $menuProduct,
                            'product' => $menuProduct->product,
                            'show_price' => $menuProduct->show_price
                        ];
                    })
                    ->filter(function($item) {
                        return $item['product'] && $item['product']->active;
                    })
                    ->values();
                
                return [
                    'slug' => $category->slug,
                    'category' => $category,
                    'products' => $products
                ];
            })
            ->filter(function ($group) {
                return $group['category'] !== null && $group['products']->isNotEmpty();
            })
            ->values();
        
        $active_category = null;
        if ($categories->isNotEmpty()) {
            if ($categorySlug) {
                $active_category = $categories->firstWhere('slug', $categorySlug);
            }
            if (!$active_category) {
                $active_category = $categories->first();
            }
        }

        // Get cart products with quantities
        $cart = session('cart', []);
        $cartProductIds = collect($cart)->pluck('product_id', 'product_id')->toArray();
        $cartQuantities = collect($cart)->mapWithKeys(function ($item) {
            return [$item['product_id'] => $item['quantity']];
        })->toArray();

        // Get favorite product IDs for authenticated users
        $favoriteProductIds = collect();
        if (auth('web')->check()) {
            $favoriteProductIds = Favourite::where('user_id', auth('web')->id())
                ->pluck('product_id');
        }

        // Get logo URL from settings
        $settingModel = \App\Models\Setting::getSettingModel();
        $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');

        return view('website.menus.menu', compact('menu', 'categories', 'active_category', 'cartProductIds', 'cartQuantities', 'favoriteProductIds', 'logoUrl'));
    }
}

