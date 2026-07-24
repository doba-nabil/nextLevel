<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\BoxDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BoxRequest;
use App\Models\Addon;
use App\Models\AddonGroup;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductDefinition;
use App\Services\BoxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BoxController extends Controller
{
    public function __construct(private BoxService $boxService)
    {
    }

    public function index(BoxDataTable $dataTable)
    {
        return $dataTable->render('dashboard.boxes.index');
    }

    public function create()
    {
        return view('dashboard.boxes.create', $this->getSharedFormData());
    }

    public function store(BoxRequest $request)
    {
//        return $request;
        $this->boxService->create(
            $request->validated(),
            $request->file('image')
        );
        $this->clearHomePageCaches();
        $this->clearCategoryCaches();
        return redirect()->route('boxes.index')->withSuccess(__('admin.save_success'));
    }

    public function edit(Product $box)
    {
        $box->load([
            'prices',
            'addons',
            'products',
            'products.boxAddons' => function ($query) use ($box) {
                $query->wherePivot('box_id', $box->id);
            }
        ]);
        $selectedProducts = $box->products->pluck('id')->toArray();
        $productAddons = [];
        $boxTitles = [];
        
        // Group products by title
        $groupedByTitle = [];
        foreach ($box->products as $product) {
            // Decode JSON title if it's a string, otherwise use as array
            $title = $product->pivot->title ?? null;
            
            // Handle different title formats
            if (is_string($title) && !empty(trim($title))) {
                $decoded = json_decode($title, true);
                $title = $decoded !== null && is_array($decoded) ? $decoded : [];
            } elseif (is_array($title) && !empty($title)) {
                // Already decoded, use as is
                $title = $title;
            } else {
                // Null or empty, set to empty array
                $title = [];
            }
            
            // Normalize title to ensure it has the expected structure
            if (empty($title) || !is_array($title)) {
                $title = ['ar' => '', 'en' => ''];
            }
            
            // Create a key for grouping based on title content only
            // Use a normalized JSON string for consistent grouping
            $titleKey = json_encode($title, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            if (!isset($groupedByTitle[$titleKey])) {
                // Get is_required, max_count, and min_count from the first product with this title
                $groupedByTitle[$titleKey] = [
                    'title' => $title,
                    'is_required' => (bool)($product->pivot->is_required ?? false),
                    'max_count' => (int)($product->pivot->max_count ?? 1),
                    'min_count' => (int)($product->pivot->min_count ?? 0),
                    'products' => []
                ];
            }
            
            $productAddons[$product->id] = $product->boxAddons->map(function ($addon) {
                return [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'type' => $addon->pivot->type ?? 'optional',
                ];
            })->toArray();
            
            // Products are just IDs now - ensure it's an integer
            $groupedByTitle[$titleKey]['products'][] = (int)$product->id;
        }
        
        // Convert grouped data to array format and sort by order
        $boxTitles = array_values($groupedByTitle);
        
        // Sort by order if available (from pivot)
        usort($boxTitles, function($a, $b) use ($box) {
            // Get order from first product in each title group
            $orderA = 0;
            $orderB = 0;
            
            if (!empty($a['products'])) {
                $firstProductId = $a['products'][0];
                $product = $box->products->firstWhere('id', $firstProductId);
                if ($product && $product->pivot) {
                    $orderA = $product->pivot->order ?? 0;
                }
            }
            
            if (!empty($b['products'])) {
                $firstProductId = $b['products'][0];
                $product = $box->products->firstWhere('id', $firstProductId);
                if ($product && $product->pivot) {
                    $orderB = $product->pivot->order ?? 0;
                }
            }
            
            return $orderA <=> $orderB;
        });
        
        return view('dashboard.boxes.edit', array_merge(
            $this->getSharedFormData(),
            compact('box', 'selectedProducts', 'productAddons', 'boxTitles')
        ));
    }


    public function update(BoxRequest $request, Product $box)
    {
        $this->boxService->update($box, $request->validated(), $request->file('image'));
        $this->clearHomePageCaches();
        $this->clearCategoryCaches();
        return redirect()->route('boxes.index')->withSuccess(__('admin.update_success'));
    }

    public function destroy($id)
    {

        try {
            $this->boxService->delete($id);
            $this->clearHomePageCaches();
            $this->clearCategoryCaches();

            return response()->json([
                'status' => 'success',
                'message' => __('admin.delete_success')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.delete_error')
            ], 500);
        }
    }

    public function toggleActive(Request $request, $id)
    {
        try {
            $box = Product::findOrFail($id);
            $box->active = $request->input('active', !$box->active);
            $box->save();
            $this->clearCategoryCaches();

            return response()->json([
                'status' => 'success',
                'message' => __('admin.update_success'),
                'active' => $box->active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.update_error')
            ], 500);
        }
    }

    /**
     * Clear all home page related caches
     */
    private function clearHomePageCaches(): void
    {
        $menuTypes = ['delivery', 'pickup'];
        
        foreach ($menuTypes as $mt) {
            // Clear home page products cache (includes offers, trending, pickup, etc.)
            Cache::forget("home_products_{$mt}");
            Cache::forget("home_products_{$mt}_city_");
            Cache::forget("categories_home_{$mt}_product");
            Cache::forget("categories_home_{$mt}_product_v2_city_");
            // Clear city-specific cache keys (for any city ID from 1 to 1000)
            for ($cityId = 1; $cityId <= 1000; $cityId++) {
                Cache::forget("home_products_{$mt}_city_{$cityId}");
                Cache::forget("categories_home_{$mt}_product_v2_city_{$cityId}");
            }
        }
        Cache::forget('burger_category');
    }

    /**
     * Clear all category-related caches
     */
    private function clearCategoryCaches(): void
    {
        $menuTypes = ['delivery', 'pickup'];
        $productTypes = [0, 1]; // 0 = product, 1 = box
        
        foreach ($menuTypes as $mt) {
            foreach ($productTypes as $pt) {
                Cache::forget("categories_{$mt}_{$pt}");
                Cache::forget("categories_{$mt}_{$pt}_city_");
                // Clear city-specific category cache keys (for any city ID from 1 to 1000)
                for ($cityId = 1; $cityId <= 1000; $cityId++) {
                    Cache::forget("categories_{$mt}_{$pt}_city_{$cityId}");
                }
            }
            Cache::forget("categories_home_{$mt}_product");
            Cache::forget("categories_home_{$mt}_product_v2_city_");
            // Clear city-specific cache keys (for any city ID from 1 to 1000)
            for ($cityId = 1; $cityId <= 1000; $cityId++) {
                Cache::forget("categories_home_{$mt}_product_v2_city_{$cityId}");
            }
        }
        // Also clear old cache key for backward compatibility
        Cache::forget('categories');
    }

    private function getSharedFormData(): array
    {
        // Get dashboard locale from URL segment (first segment in admin routes is locale)
        // Fallback to session, then config
        $locale = request()->segment(1) ?: (session('admin_locale') ?: config('app.locale'));
        
        $products = Product::where('is_box', false)->get()->map(function($product) {
            $nameArray = $product->name;
            
            if (is_string($nameArray)) {
                $nameArray = json_decode($nameArray, true) ?? [];
            }
            
            if (!is_array($nameArray)) {
                $nameArray = [];
            }
            
            $arName = !empty($nameArray['ar']) ? $nameArray['ar'] : ($product->getTranslation('name', 'ar', false) ?: '');
            $enName = !empty($nameArray['en']) ? $nameArray['en'] : ($product->getTranslation('name', 'en', false) ?: '');
            
            return [
                'id' => $product->id,
                'name' => [
                    'ar' => $arName,
                    'en' => $enName,
                ],
            ];
        });
        
        return [
            'currencies' => Currency::all(),
            'addons' => Addon::all(),
            'all_addons' => Addon::all(),
            'definitions' => ProductDefinition::all(),
            'categories' => Category::all(),
            'branches' => Branch::all(),
            'addon_groups' => AddonGroup::whereHas('addons')->get(),
            'products' => $products,
        ];
    }
}

