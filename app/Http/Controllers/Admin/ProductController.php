<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\MealDataTable;
use App\DataTables\ProductDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Resources\AddonResource;
use App\Imports\AddonsImport;
use App\Imports\ProductsImport;
use App\Models\Addon;
use App\Models\AddonGroup;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductDefinition;
use App\Models\ProductNote;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService)
    {
    }

    public function index()
    {
        $type = request()->route()->getName();
        if (str_contains($type, 'meals')) {
            $dataTable = new MealDataTable();
            return $dataTable->render('dashboard.meals.index');
        } else {
            $dataTable = new ProductDataTable();
            return $dataTable->render('dashboard.products.index');
        }
    }

    public function create()
    {
        $type = request()->route()->getName();
        if (str_contains($type, 'meals')) {
            return view('dashboard.meals.create', $this->getSharedFormData());
        } else {
            return view('dashboard.products.create', $this->getSharedFormData());
        }
    }

    public function store(ProductRequest $request)
    {
//        return $request;
        $this->productService->create(
            $request->validated(),
            $request->file('image')
        );
        $this->clearCategoryCaches();
        return redirect()->route($request->product_type.'s.index')->withSuccess(__('admin.add_success'));
    }

    public function edit(Product $product)
    {
        $type = request()->route()->getName();
        $product->load(['prices', 'addons', 'definitions']);
        if (str_contains($type, 'meals')) {
            return view('dashboard.meals.edit', array_merge($this->getSharedFormData(), compact('product')));
        } else {
            return view('dashboard.products.edit', array_merge($this->getSharedFormData(), compact('product')));
        }
    }

    public function update(ProductRequest $request, Product $product)
    {
        $this->productService->update($product, $request->validated(), $request->file('image'));
        $this->clearCategoryCaches();
        
        // Check if there's a return URL (e.g., from categories page)
        if ($request->has('return_to') && filter_var($request->return_to, FILTER_VALIDATE_URL)) {
            return redirect($request->return_to)->withSuccess(__('admin.update_success'));
        }
        
        return redirect()->route($request->product_type.'s.index')->withSuccess(__('admin.update_success'));
    }

    public function destroy(Product $product)
    {
        try {
            $this->productService->delete($product);
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

    public function toggleActive(Request $request, Product $product)
    {
        try {
            $product->active = $request->input('active', !$product->active);
            $product->save();
            $this->clearCategoryCaches();

            return response()->json([
                'status' => 'success',
                'message' => __('admin.update_success'),
                'active' => $product->active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.update_error')
            ], 500);
        }
    }

    public function getAddons($id)
    {
        $product = Product::with('addons')->findOrFail($id);
        $allAddons = Addon::select('id', 'name')->get();
        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
            ],
            'addons' => AddonResource::collection($product->addons()->get()),
            'all_addons' => $allAddons->map(function ($a) {
                return [
                    'id' => $a->id,
                    'name' => $a->name,
                ];
            }),
        ]);
    }

    public function getAddonsEdit($id)
    {
        $product = Product::with('addons')->findOrFail($id);
        $allAddons = Addon::select('id', 'name')->get();
        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
            ],
            'addons' => AddonResource::collection($product->boxAddons()->wherePivot('box_id', \request('box_id'))->get()),
            'all_addons' => $allAddons->map(function ($a) {
                return [
                    'id' => $a->id,
                    'name' => $a->name,
                ];
            }),
        ]);
    }

    public function prices($id)
    {
        $product = Product::with('prices.currency')->findOrFail($id);
        return view('dashboard.products.partials.prices_table', compact('product'));
    }

    public function getProductNotes($id)
    {
        $product = Product::findOrFail($id);
        $notes = ProductNote::where('product_id', $product->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.products.partials.notes_table', compact('product', 'notes'));
    }

    private function getSharedFormData(): array
    {
        return [
            'currencies' => Currency::all(),
            'addons' => Addon::all(),
            'all_addons' => Addon::all(),
            'definitions' => ProductDefinition::all(),
            'categories' => Category::all(),
            'branches' => Branch::all(),
            'addon_groups' => AddonGroup::whereHas('addons')->get(),
        ];
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new ProductsImport, $request->file('file'));
        $this->clearCategoryCaches();
        return back()->with('success', __('admin.import_success'));
    }

    /**
     * Clear all category-related caches and home page product caches
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
            // Clear home page products cache (includes offers, trending, pickup, etc.)
            Cache::forget("home_products_{$mt}");
            Cache::forget("home_products_{$mt}_city_");
        }
        // Also clear old cache key for backward compatibility
        Cache::forget('categories');
        Cache::forget('burger_category');
        
        // Clear all city-specific caches (for any city ID from 1 to 1000)
        foreach ($menuTypes as $mt) {
            for ($cityId = 1; $cityId <= 1000; $cityId++) {
                Cache::forget("home_products_{$mt}_city_{$cityId}");
                Cache::forget("categories_home_{$mt}_product_v2_city_{$cityId}");
            }
        }
    }

    /**
     * Display meals categories and products with drag and drop ordering
     */
    public function mealsCategoriesOrder()
    {
        // Get all categories that have meals, ordered by order field
        $categories = Category::whereHas('products', function($query) {
            $query->where('product_type', 'meal');
        })
        ->with(['products' => function($query) {
            $query->where('product_type', 'meal')
                  ->orderBy('order', 'asc')
                  ->orderBy('id', 'asc');
        }])
        ->orderBy('order', 'asc')
        ->orderBy('id', 'asc')
        ->get();

        // Get meals without category
        $mealsWithoutCategory = Product::where('product_type', 'meal')
            ->whereNull('category_id')
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return view('dashboard.meals.categories-order', compact('categories', 'mealsWithoutCategory'));
    }

    /**
     * Update categories order
     */
    public function updateCategoriesOrder(Request $request)
    {
        try {
            $request->validate([
                'categories' => 'required|array',
                'categories.*.category_id' => 'required|exists:categories,id',
                'categories.*.order' => 'required|integer|min:0',
            ]);

            DB::beginTransaction();

            foreach ($request->input('categories') as $categoryData) {
                Category::where('id', $categoryData['category_id'])
                    ->update(['order' => $categoryData['order']]);
            }

            DB::commit();

            // Clear cache
            $this->clearCategoryCaches();

            return response()->json([
                'status' => 'success',
                'message' => __('admin.update_success')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => __('admin.update_error'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update meals order within categories
     */
    public function updateMealsOrder(Request $request)
    {
        try {
            $request->validate([
                'meals' => 'required|array',
                'meals.*.product_id' => 'required|exists:products,id',
                'meals.*.order' => 'required|integer|min:0',
                'meals.*.category_id' => 'nullable|exists:categories,id',
            ]);

            DB::beginTransaction();

            foreach ($request->input('meals') as $mealData) {
                Product::where('id', $mealData['product_id'])
                    ->where('product_type', 'meal')
                    ->update([
                        'order' => $mealData['order'],
                        'category_id' => $mealData['category_id'] ?? null
                    ]);
            }

            DB::commit();

            // Clear cache
            $this->clearCategoryCaches();

            return response()->json([
                'status' => 'success',
                'message' => __('admin.update_success')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => __('admin.update_error'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

