<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheController extends Controller
{
    /**
     * Clear all cache
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear(Request $request)
    {
        try {
            // Clear application cache
            Cache::flush();
            
            // Clear config cache
            Artisan::call('config:clear');
            
            // Clear route cache
            Artisan::call('route:clear');
            
            // Clear view cache
            Artisan::call('view:clear');
            
            // Clear compiled classes
            Artisan::call('clear-compiled');
            
            // Clear application cache (Laravel cache)
            Artisan::call('cache:clear');

            Log::info('Cache cleared via API');

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully',
                'cleared' => [
                    'application_cache' => true,
                    'config_cache' => true,
                    'route_cache' => true,
                    'view_cache' => true,
                    'compiled_classes' => true
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error clearing cache', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache: ' . $e->getMessage()
            ], 500);
        }
    }
    
     public function clearCategoriesCache(Request $request)
    {
        try {
            $menuTypes = ['delivery', 'pickup'];
            $productTypes = [0, 1]; // 0 = product, 1 = box
            $cleared = 0;

            foreach ($menuTypes as $mt) {
                foreach ($productTypes as $pt) {
                    Cache::forget("categories_{$mt}_{$pt}");
                    Cache::forget("categories_{$mt}_{$pt}_city_");
                    $cleared += 2;

                    // Clear city-specific category cache keys (for any city ID from 1 to 1000)
                    for ($cityId = 1; $cityId <= 1000; $cityId++) {
                        Cache::forget("categories_{$mt}_{$pt}_city_{$cityId}");
                        $cleared++;
                    }
                }

                Cache::forget("categories_home_{$mt}_product");
                Cache::forget("categories_home_{$mt}_product_v2_city_");
                $cleared += 2;

                // Clear city-specific cache keys (for any city ID from 1 to 1000)
                for ($cityId = 1; $cityId <= 1000; $cityId++) {
                    Cache::forget("categories_home_{$mt}_product_v2_city_{$cityId}");
                    Cache::forget("home_products_{$mt}_city_{$cityId}");
                    $cleared += 2;
                }

                Cache::forget("home_products_{$mt}");
                Cache::forget("home_products_{$mt}_city_");
                Cache::forget("menus_active_{$mt}");
                $cleared += 3;
            }

            // Clear other related caches
            Cache::forget('categories');
            Cache::forget('burger_category');
            Cache::forget('website_sliders');
            $cleared += 3;

            Log::info('Categories cache cleared via API', [
                'total_keys_cleared' => $cleared
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categories cache cleared successfully',
                'total_keys_cleared' => $cleared
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error clearing categories cache', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error clearing categories cache: ' . $e->getMessage()
            ], 500);
        }
    }
}
