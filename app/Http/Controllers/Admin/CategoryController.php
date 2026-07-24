<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\CategoryDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService) {}


    public function index(CategoryDataTable $dataTable)
    {
        return $dataTable->render('dashboard.categories.index');
    }

    public function create()
    {
        $menus = \App\Models\Menu::where('is_active', true)->get();
        return view('dashboard.categories.create', compact('menus'));
    }

    public function store(CategoryRequest $request)
    {
        $this->categoryService->create(
            $request->validated(),
            $request->file('image')
        );
        $this->clearCategoryCaches();
        return redirect()->route('categories.index')->with('success', __('admin.save_success'));
    }

    public function edit($id)
    {
        $category = $this->categoryService->getById($id);
        $menus = \App\Models\Menu::where('is_active', true)->get();
        return view('dashboard.categories.edit', compact('category', 'menus'));
    }

    public function update(CategoryRequest $request, $id)
    {
        $category = $this->categoryService->getById($id);
        $data = $request->validated();
        
        // Handle products order if provided
        if ($request->has('products_order') && is_array($request->products_order)) {
            foreach ($request->products_order as $productId => $order) {
                \App\Models\Product::where('id', $productId)
                    ->where('category_id', $category->id)
                    ->update(['order' => (int) $order]);
            }
        }
        
        $this->categoryService->update($category, $data, $request->file('image'));
        $this->clearCategoryCaches();
        return redirect()->route('categories.index')->with('success', __('admin.update_success'));
    }

    public function destroy($id)
    {
        try {
            $this->categoryService->delete($id);
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
            $category = $this->categoryService->getById($id);
            $category->active = $request->input('active', !$category->active);
            $category->save();
            $this->clearCategoryCaches();

            return response()->json([
                'status' => 'success',
                'message' => __('admin.update_success'),
                'active' => $category->active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.update_error')
            ], 500);
        }
    }

    public function getProducts($id)
    {
        try {
            $category = $this->categoryService->getById($id);
            $locale = app()->getLocale();
            
            $products = $category->products()->orderBy('order', 'asc')->get()->map(function($product) use ($locale) {
                // Determine edit URL based on product type
                $editUrl = route('products.edit', $product->id);
                
                if ($product->product_type === 'meal') {
                    $editUrl = route('meals.edit', $product->id);
                } elseif ($product->product_type === 'box' || $product->is_box == 1) {
                    $editUrl = route('boxes.edit', $product->id);
                }
                
                return [
                    'id' => $product->id,
                    'name' => $product->getTranslation('name', $locale),
                    'active' => $product->active,
                    'slug' => $product->slug,
                    'order' => $product->order ?? 0,
                    'edit_url' => $editUrl,
                ];
            });

            return response()->json([
                'status' => 'success',
                'products' => $products,
                'count' => $products->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.error_occurred')
            ], 500);
        }
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
                // Clear city-specific cache keys (for any city ID from 1 to 1000)
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

}
