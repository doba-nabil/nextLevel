<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearProductCityCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-product-city';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all product and category caches related to cities';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing product and category caches...');

        $menuTypes = ['delivery', 'pickup'];
        $productTypes = [0, 1]; // 0 = product, 1 = box
        
        // Clear all city-related caches
        foreach ($menuTypes as $mt) {
            foreach ($productTypes as $pt) {
                // Clear category caches with city
                Cache::forget("categories_{$mt}_{$pt}_city_");
                Cache::forget("categories_{$mt}_{$pt}");
            }
            
            // Clear home page caches
            Cache::forget("categories_home_{$mt}_product");
            Cache::forget("categories_home_{$mt}_product_v2_city_");
            Cache::forget("home_products_{$mt}");
            Cache::forget("home_products_{$mt}_city_");
            Cache::forget("menus_active_{$mt}");
            
            // Clear all possible city-specific caches (for any city ID)
            // Since cache keys include city ID, we need to clear the pattern
            // We'll use a more aggressive approach - clear all cache tags if using tags
            // Or clear specific patterns
            for ($cityId = 1; $cityId <= 1000; $cityId++) {
                Cache::forget("home_products_{$mt}_city_{$cityId}");
                Cache::forget("categories_home_{$mt}_product_v2_city_{$cityId}");
            }
        }
        
        // Clear other related caches
        Cache::forget('burger_category');
        Cache::forget('categories');
        Cache::forget('website_sliders');
        
        // Clear all cache if using file/database cache driver
        if (config('cache.default') === 'file' || config('cache.default') === 'database') {
            $this->info('Clearing all application cache...');
            Cache::flush();
        }

        $this->info('✅ Product and category caches cleared successfully!');
        $this->info('Please refresh your website to see updated products.');
        
        return Command::SUCCESS;
    }
}


