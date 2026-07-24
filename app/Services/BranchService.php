<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BranchWorkingHour;
use App\Traits\mediaUploader;
use App\Traits\slugGenerator;
use Illuminate\Support\Facades\Cache;

class BranchService
{
    use  slugGenerator;
    public function getAll()
    {
        return Branch::all();
    }

    public function getById($id)
    {
        return Branch::findOrFail($id);
    }
    public function create(array $data): Branch
    {
        // Validate cities
        if (!isset($data['cities']) || empty($data['cities'])) {
            throw new \Exception('يجب اختيار مدينة واحدة على الأقل.');
        }
        
        // Set location_id to first city for backward compatibility
        $data['location_id'] = $data['cities'][0];
        
        // Map latitude/longitude to lat/lng (always unset to avoid conflicts)
        if (isset($data['latitude'])) {
            if ($data['latitude'] !== null && $data['latitude'] !== '') {
                $data['lat'] = (float) $data['latitude'];
            }
            unset($data['latitude']);
        }
        
        if (isset($data['longitude'])) {
            if ($data['longitude'] !== null && $data['longitude'] !== '') {
                $data['lng'] = (float) $data['longitude'];
            }
            unset($data['longitude']);
        }
        
        $data['slug'] = $this->generateSlug($data);
        $branch = Branch::create($data);
        
        // Sync cities
        if (isset($data['cities'])) {
            $branch->cities()->sync($data['cities']);
            // Clear cache after updating cities
            $this->clearProductCityCaches();
        }
        
        if (isset($data['working_hours'])) {
            foreach ($data['working_hours'] as $hour) {
                $branch->workingHours()->create($hour);
            }
        } else {
            $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            foreach ($days as $day) {
                $branch->workingHours()->create([
                    'from_day' => $day,
                    'to_day' => $day,
                    'from_time' => '09:00:00',
                    'to_time' => '22:00:00',
                ]);
            }
        }
        return $branch;
    }

    public function update(Branch $branch, array $data): Branch
    {
        // Validate cities
        if (!isset($data['cities']) || empty($data['cities'])) {
            throw new \Exception('يجب اختيار مدينة واحدة على الأقل.');
        }
        
        // Set location_id to first city for backward compatibility
        $data['location_id'] = $data['cities'][0];
        
        // Map latitude/longitude to lat/lng (always unset to avoid conflicts)
        if (isset($data['latitude'])) {
            if ($data['latitude'] !== null && $data['latitude'] !== '') {
                $data['lat'] = (float) $data['latitude'];
            }
            unset($data['latitude']);
        }
        
        if (isset($data['longitude'])) {
            if ($data['longitude'] !== null && $data['longitude'] !== '') {
                $data['lng'] = (float) $data['longitude'];
            }
            unset($data['longitude']);
        }
        

        if (empty($data['password'])) {
            unset($data['password']);
        }
        
        $data['slug'] = $this->generateSlug($data);
        $branch->update($data);
        
        // Sync cities
        if (isset($data['cities'])) {
            $branch->cities()->sync($data['cities']);
            // Clear cache after updating cities
            $this->clearProductCityCaches();
        }
        
        if (isset($data['working_hours'])) {
            $branch->workingHours()->delete();
            foreach ($data['working_hours'] as $hour) {
                $branch->workingHours()->create($hour);
            }
        }

        return $branch;
    }

    public function delete($id)
    {
        $branch = Branch::findOrFail($id);
        return $branch->delete();
    }

    public function getBranchesWithHours()
    {
        return Branch::with('location', 'workingHours')->where('is_active', true)->get();
    }

    /**
     * Clear all product and category caches related to cities
     * This should be called whenever branch cities are updated
     */
    private function clearProductCityCaches(): void
    {
        $menuTypes = ['delivery', 'pickup'];
        $productTypes = [0, 1]; // 0 = product, 1 = box
        
        // Clear all city-related caches
        foreach ($menuTypes as $mt) {
            foreach ($productTypes as $pt) {
                // Clear category caches with city
                Cache::forget("categories_{$mt}_{$pt}_city_");
                Cache::forget("categories_{$mt}_{$pt}");
                // Clear city-specific category cache keys (for any city ID from 1 to 1000)
                for ($cityId = 1; $cityId <= 1000; $cityId++) {
                    Cache::forget("categories_{$mt}_{$pt}_city_{$cityId}");
                }
            }
            
            // Clear home page caches
            Cache::forget("categories_home_{$mt}_product");
            Cache::forget("categories_home_{$mt}_product_v2_city_");
            Cache::forget("home_products_{$mt}");
            Cache::forget("home_products_{$mt}_city_");
            Cache::forget("menus_active_{$mt}");
            
            // Clear all city-specific caches (for any city ID from 1 to 1000)
            for ($cityId = 1; $cityId <= 1000; $cityId++) {
                Cache::forget("home_products_{$mt}_city_{$cityId}");
                Cache::forget("categories_home_{$mt}_product_v2_city_{$cityId}");
            }
        }
        
        // Clear other related caches
        Cache::forget('burger_category');
        Cache::forget('categories');
        Cache::forget('website_sliders');
    }
}
