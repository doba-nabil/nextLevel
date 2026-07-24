<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use App\Models\Slider;
use App\Models\Favourite;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function home()
    {
        $menuType = session('menu_type', 'delivery');
        $locale = app()->getLocale();

        // Optimize: Cache menus query
        $allMenus = Cache::remember("menus_active_{$menuType}", 3600, function () {
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
                ->get(['id', 'name', 'content', 'slug']);
        });

        // Optimize: Cache sliders with media eager loading
        $sliders = Cache::rememberForever('website_sliders_v2', function () {
            return Slider::where('active', 1)
                ->with('media')
                ->get(['id', 'big_title', 'small_title', 'content', 'url']);
        });

        // Get selected city from session
        $userLocation = session('user_location');
        $cityId = $userLocation['city_id'] ?? null;
        
        // Check if pickup products are available
        $hasPickupProducts = Cache::remember("has_pickup_products_city_{$cityId}", 1800, function () use ($cityId) {
            return Product::where('is_box', 0)
                ->where('active', true)
                ->where(function ($q) {
                    $q->where('type', 'pickup')->orWhere('type', 'both');
                })
                ->whereHas('branches', function($q) {
                    $q->where('branches.active', true)
                      ->where('product_branches.status', 'available');
                })
                ->byCity($cityId)
                ->exists();
        });
        
        // Optimize: Cache product queries with better eager loading
        $productCacheKey = "home_products_{$menuType}_city_{$cityId}";
        $productData = Cache::remember($productCacheKey, 1800, function () use ($menuType, $cityId) {
            $pickupProducts = Product::where('is_box', 0)
                ->where('is_pickup', true)
                ->where('active', true)
                ->where(function ($q) use ($menuType) {
                    $q->where('type', $menuType)->orWhere('type', 'both');
                })
                ->whereHas('branches')
                ->byCity($cityId)
                ->with(['definitions', 'addonGroups' => function($q) {
                    $q->where('active', 1);
                }, 'addons' => function($q) {
                    $q->where('active', 1);
                }])
                ->orderBy('order')
                ->orderBy('id')
                ->limit(4)
                ->get();

            // Trending products
            $trendingProducts = Product::where('is_box', 0)
                ->where('is_trending', true)
                ->where('active', true)
                ->where(function ($q) use ($menuType) {
                    $q->where('type', $menuType)->orWhere('type', 'both');
                })
                ->byCity($cityId)
                ->with(['definitions', 'addonGroups' => function($q) {
                    $q->where('active', 1);
                }, 'addons' => function($q) {
                    $q->where('active', 1);
                }])
                ->orderBy('order')
                ->orderBy('id')
                ->limit(12)
                ->get();

            // New plates products
            $newPlatesProducts = Product::where('is_box', 0)
                ->where('is_new_plates', true)
                ->where('active', true)
                ->where(function ($q) use ($menuType) {
                    $q->where('type', $menuType)->orWhere('type', 'both');
                })
                ->byCity($cityId)
                ->with(['definitions', 'addonGroups' => function($q) {
                    $q->where('active', 1);
                }, 'addons' => function($q) {
                    $q->where('active', 1);
                }])
                ->orderBy('order')
                ->orderBy('id')
                ->limit(4)
                ->get();

            // Burger products - optimize whereHas with direct category_id
            $burgerCategoryId = Category::where('active', 1)
                ->where('name->en', 'Burger')
                ->value('id');

            $products = Product::where('is_box', 0)
                ->where('active', true)
                ->when($burgerCategoryId, function($q) use ($burgerCategoryId) {
                    $q->where('category_id', $burgerCategoryId);
                })
                ->where(function ($q) use ($menuType) {
                    $q->where('type', $menuType)->orWhere('type', 'both');
                })
                ->byCity($cityId)
                ->with(['definitions', 'addonGroups' => function($q) {
                    $q->where('active', 1);
                }, 'addons' => function($q) {
                    $q->where('active', 1);
                }])
                ->orderBy('order')
                ->orderBy('id')
                ->limit(12)
                ->get();

            $offers = Product::where('is_box', 0)
                ->where('active', true)
                ->where('show_in_limit_offer', true)
                ->where(function ($q) use ($menuType) {
                    $q->where('type', $menuType)->orWhere('type', 'both');
                })
                ->with(['definitions', 'prices' => function($q) {
                    $q->where('has_discount', 1);
                }])
                ->orderBy('order')
                ->orderBy('id')
                ->limit(10)
                ->get();

            return [
                'pickupProducts' => $pickupProducts,
                'trendingProducts' => $trendingProducts,
                'newPlatesProducts' => $newPlatesProducts,
                'products' => $products,
                'offers' => $offers,
            ];
        });

        $pickupProducts = $productData['pickupProducts'];
        $trendingProducts = $productData['trendingProducts'];
        $newPlatesProducts = $productData['newPlatesProducts'];
        $products = $productData['products'];
        $offers = $productData['offers'];

        // Get burger category for "See All" link - cache it
        $burgerCategory = Cache::remember('burger_category', 3600, function () {
            return Category::where('active', 1)
                ->where('name->en', 'Burger')
                ->first(['id', 'name', 'slug']);
        });

        // Get all categories with their products for home page
        $cacheKey = 'categories_home_' . $menuType . '_product_v2_city_' . $cityId;
        $categories = Cache::rememberForever($cacheKey, function () use ($menuType, $cityId) {
            return Category::where('active', 1)
                ->whereHas('products', function ($query) use ($menuType, $cityId) {
                    $query->where('active', true)
                        ->where(function ($q) use ($menuType) {
                            $q->where('type', $menuType)->orWhere('type', 'both');
                        })
                        ->byCity($cityId);
                })
                ->withCount(['products as products_count' => function ($query) use ($menuType, $cityId) {
                    $query->where('is_box', 0)
                        ->where('active', true)
                        ->where(function ($q) use ($menuType) {
                            $q->where('type', $menuType)->orWhere('type', 'both');
                        })
                        ->byCity($cityId);
                }])
                ->withCount(['products as boxes_count' => function ($query) use ($menuType, $cityId) {
                    $query->where('is_box', 1)
                        ->where('active', true)
                        ->where(function ($q) use ($menuType) {
                            $q->where('type', $menuType)->orWhere('type', 'both');
                        })
                        ->byCity($cityId);
                }])
                ->with(['products' => function ($query) use ($menuType, $cityId) {
                    $query->where('active', true)
                        ->where(function ($q) use ($menuType) {
                            $q->where('type', $menuType)->orWhere('type', 'both');
                        })
                        ->byCity($cityId)
                        ->with(['addonGroups', 'addons', 'definitions'])
                        ->orderBy('is_box', 'asc')
                        ->orderBy('order', 'asc')
                        ->orderBy('id', 'asc')
                        ->limit(12);
                }])
                ->with('media')
                ->orderBy('order')
                ->orderBy('id')
                ->get()
                ->map(function ($category) {
                    $category->total_count = ($category->products_count ?? 0) + ($category->boxes_count ?? 0);
                    $category->image_url = $category->getFirstMediaUrl('categories');
                    return $category;
                })
                ->filter(function ($category) {
                    return ($category->products_count > 0) || ($category->boxes_count > 0);
                })
                ->values();
        });

        // Optimize: Add eager loading for boxes
        $boxes = Product::where('is_box', 1)
            ->where('active', true)
            ->where('is_home', 1)
            ->where(function ($q) use ($menuType) {
                $q->where('type', $menuType)->orWhere('type', 'both');
            })
            ->byCity($cityId)
            ->with(['definitions', 'addonGroups' => function($q) {
                $q->where('active', 1);
            }, 'addons' => function($q) {
                $q->where('active', 1);
            }])
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $specialCategories = collect();
        
        $boxesProducts = Product::where('active', true)
            ->where('is_box', true)
            ->where(function ($q) use ($menuType) {
                $q->where('type', $menuType)->orWhere('type', 'both');
            })
            ->byCity($cityId)
            ->with(['addonGroups', 'addons', 'definitions'])
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->limit(12)
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
            ->with(['addonGroups', 'addons', 'definitions'])
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->limit(12)
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
            ->with(['addonGroups', 'addons', 'definitions'])
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->limit(12)
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
            ->with(['addonGroups', 'addons', 'definitions'])
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->limit(12)
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

        // Optimize: Single query for favorites
        $favoriteProductIds = collect();
        if (auth('web')->check()) {
            $favoriteProductIds = Favourite::where('user_id', auth('web')->id())
                ->pluck('product_id');
        }

        // Get cart products with quantities
        $cart = session('cart', []);
        $cartProductIds = collect($cart)->pluck('product_id', 'product_id')->toArray();
        $cartQuantities = collect($cart)->mapWithKeys(function ($item) {
            return [$item['product_id'] => $item['quantity']];
        })->toArray();

        // Optimize: Fetch all settings in one query
        $settingsKeys = [
            'banner_title', 'banner_subtitle', 'banner_content', 'banner_link',
            'show_banner_section', 'show_slider_section_desktop', 'show_slider_section_mobile',
            'show_categories_section_desktop', 'show_categories_section_mobile',
            'show_menu_section_desktop', 'show_menu_section_mobile',
            'show_pickup_section_desktop', 'show_pickup_section_mobile',
            'show_new_plates_section_desktop', 'show_new_plates_section_mobile',
            'show_boxes_section_desktop', 'show_boxes_section_mobile',
            'show_offers_section_desktop', 'show_offers_section_mobile',
            'show_burger_section_desktop', 'show_burger_section_mobile',
            'home_banner'
        ];

        $settings = Cache::remember('home_settings_' . $locale, 3600, function () use ($settingsKeys, $locale) {
            return Setting::whereIn('key', $settingsKeys)
                ->with('media')
                ->get()
                ->keyBy('key');
        });

        // Helper function to get setting value
        $getSettingValue = function($key, $default = null) use ($settings, $locale) {
            $setting = $settings->get($key);
            if (!$setting) return $default;

            if ($setting->translatable_value) {
                $decoded = json_decode($setting->translatable_value, true);
                return $decoded[$locale] ?? $default;
            }

            return $setting->value ?? $default;
        };

        // Get banners from database
        $banners = Cache::remember("website_banners_{$locale}", 3600, function () use ($locale) {
            return Banner::active()
                ->ordered()
                ->with('media')
                ->get()
                ->map(function ($banner) use ($locale) {
                    // Get image based on current locale
                    $collection = $locale === 'ar' ? 'banner_image_ar' : 'banner_image_en';
                    $banner->image_url = $banner->getFirstMediaUrl($collection);
                    return $banner;
                })
                ->filter(function ($banner) {
                    // Only show banners that have an image for the current locale
                    return !empty($banner->image_url);
                });
        });
        
        // Get section visibility (desktop and mobile)
        $showBannerSection = $getSettingValue('show_banner_section', '1') == '1';
        $showSliderSectionDesktop = $getSettingValue('show_slider_section_desktop', '1') == '1';
        $showSliderSectionMobile = $getSettingValue('show_slider_section_mobile', '1') == '1';
        $showCategoriesSectionDesktop = $getSettingValue('show_categories_section_desktop', '1') == '1';
        $showCategoriesSectionMobile = $getSettingValue('show_categories_section_mobile', '1') == '1';
        $showMenuSectionDesktop = $getSettingValue('show_menu_section_desktop', '1') == '1';
        $showMenuSectionMobile = $getSettingValue('show_menu_section_mobile', '1') == '1';
        $showPickupSectionDesktop = $getSettingValue('show_pickup_section_desktop', '1') == '1';
        $showPickupSectionMobile = $getSettingValue('show_pickup_section_mobile', '1') == '1';
        $showNewPlatesSectionDesktop = $getSettingValue('show_new_plates_section_desktop', '1') == '1';
        $showNewPlatesSectionMobile = $getSettingValue('show_new_plates_section_mobile', '1') == '1';
        $showBoxesSectionDesktop = $getSettingValue('show_boxes_section_desktop', '1') == '1';
        $showBoxesSectionMobile = $getSettingValue('show_boxes_section_mobile', '1') == '1';
        $showOffersSectionDesktop = $getSettingValue('show_offers_section_desktop', '1') == '1';
        $showOffersSectionMobile = $getSettingValue('show_offers_section_mobile', '1') == '1';
        $showTrendingSectionDesktop = $getSettingValue('show_trending_section_desktop', '1') == '1';
        $showTrendingSectionMobile = $getSettingValue('show_trending_section_mobile', '1') == '1';
        $showBurgerSectionDesktop = $getSettingValue('show_burger_section_desktop', '1') == '1';
        $showBurgerSectionMobile = $getSettingValue('show_burger_section_mobile', '1') == '1';

        // Get logo URL from database
        $settingModel = Setting::getSettingModel();
        $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');

        return view('website.home', compact(
            'offers', 'allMenus', 'sliders', 'categories', 'products', 
            'pickupProducts', 'trendingProducts', 'newPlatesProducts', 'boxes', 
            'favoriteProductIds', 'cartProductIds', 'cartQuantities',
            'banners', 'showBannerSection', 'hasPickupProducts', 'logoUrl',
            'showSliderSectionDesktop', 'showSliderSectionMobile',
            'showCategoriesSectionDesktop', 'showCategoriesSectionMobile',
            'showMenuSectionDesktop', 'showMenuSectionMobile',
            'showPickupSectionDesktop', 'showPickupSectionMobile',
            'showNewPlatesSectionDesktop', 'showNewPlatesSectionMobile',
            'showBoxesSectionDesktop', 'showBoxesSectionMobile',
            'showOffersSectionDesktop', 'showOffersSectionMobile',
            'showTrendingSectionDesktop', 'showTrendingSectionMobile',
            'showBurgerSectionDesktop', 'showBurgerSectionMobile',
            'burgerCategory'
        ));
    }

    public function menu_type()
    {
        $type = request('type', 'delivery');
        $currentType = session('menu_type', 'delivery');
        
        // If switching from pickup to delivery, clear pickup-related session data
        if ($currentType === 'pickup' && $type === 'delivery') {
            session()->forget(['pickup_branch_id', 'pickup_scheduled_date', 'pickup_scheduled_time', 'pickup_governorate_id', 'pickup_city_id']);
        }
        
        // If switching from delivery to pickup, clear pickup-related session data to force re-selection
        if ($currentType === 'delivery' && $type === 'pickup') {
            session()->forget(['pickup_branch_id', 'pickup_scheduled_date', 'pickup_scheduled_time', 'pickup_governorate_id', 'pickup_city_id']);
        }
        
        session(['menu_type' => $type]);
        // Clear all related caches for all menu types
        $menuTypes = ['delivery', 'pickup'];
        $productTypes = [0, 1]; // 0 = product, 1 = box
        foreach ($menuTypes as $mt) {
            foreach ($productTypes as $pt) {
                Cache::forget("categories_{$mt}_{$pt}_city_");
            }
            Cache::forget("categories_home_{$mt}_product");
            Cache::forget("categories_home_{$mt}_product_v2_city_");
            Cache::forget("home_products_{$mt}");
            Cache::forget("home_products_{$mt}_city_");
            Cache::forget("menus_active_{$mt}");
        }
        // Clear burger category cache
        Cache::forget('burger_category');
        return redirect()->back();
    }
}
