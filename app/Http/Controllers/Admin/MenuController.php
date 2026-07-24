<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\MenuDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuRequest;
use App\Models\Category;
use App\Services\MenuService;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(private MenuService $menuService) {}

    public function index(MenuDataTable $dataTable)
    {
        return $dataTable->render('dashboard.menus.index');
    }

    public function create()
    {
        $products = \App\Models\Product::where('active', true)->get();
        $categories = Category::all();
        return view('dashboard.menus.create', compact('products','categories'));
    }

    public function store(MenuRequest $request)
    {
        $this->menuService->create(
            $request->validated(),
            $request->file('image')
        );
        return redirect()->route('menus.index')->with('success', __('admin.save_success'));
    }

    public function edit($id)
    {
        $menu = Menu::with(['products.category', 'menuProducts.product', 'menuProducts.category'])->findOrFail($id);
        $products = \App\Models\Product::where('active', true)->get();
        $categories = Category::all();
        return view('dashboard.menus.edit', compact('menu', 'products','categories'));
    }

    public function update(MenuRequest $request, $id)
    {
        $menu = $this->menuService->getById($id);
        $this->menuService->update($menu, $request->validated(), $request->file('image'));
        return redirect()->route('menus.index')->with('success', __('admin.update_success'));
    }

    public function destroy($id)
    {
        try {
            $this->menuService->delete($id);

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

    public function getCategories(Request $request)
    {
        $locale = app()->getLocale();
        $categories = \App\Models\Category::where('active', true)
            ->get(['id', 'name', 'slug'])
            ->map(function($category) use ($locale) {
                return [
                    'id' => $category->id,
                    'name' => [
                        'ar' => $category->getTranslation('name', 'ar'),
                        'en' => $category->getTranslation('name', 'en'),
                    ],
                    'translated_name' => $category->getTranslation('name', $locale),
                    'slug' => $category->slug,
                ];
            });

        return response()->json($categories);
    }

    public function getProducts(Request $request)
    {
        $categoryId = $request->input('category_id');
        $search = $request->input('search', '');

        $query = \App\Models\Product::where('active', true);

        // If category is provided, filter by category first
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // If search term is provided (at least 2 characters), also search in product names
        if (strlen($search) >= 2) {
            $query->where(function($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"]);
            });
        }

        $locale = app()->getLocale();
        $products = $query->with('category:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'category_id']);

        // Format products with category name
        $formattedProducts = $products->map(function($product) use ($locale) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'category_id' => $product->category_id,
                'category_name' => $product->category ? [
                    'ar' => $product->category->getTranslation('name', 'ar'),
                    'en' => $product->category->getTranslation('name', 'en'),
                    'translated' => $product->category->getTranslation('name', $locale)
                ] : null
            ];
        });

        return response()->json($formattedProducts);
    }

    public function toggleActive(Request $request, $id)
    {
        try {
            $menu = $this->menuService->getById($id);
            $menu->is_active = $request->input('active', !$menu->is_active);
            $menu->save();

            return response()->json([
                'status' => 'success',
                'message' => __('admin.update_success'),
                'active' => $menu->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.update_error')
            ], 500);
        }
    }

    /**
     * Display categories and products for a specific menu
     */
    public function categories($id)
    {
        $menu = Menu::with([
            'menuProducts.product',
            'menuProducts.category'
        ])->findOrFail($id);

        // Group products by category
        $categoriesWithProducts = [];

        // Get all unique categories for this menu
        $categoryIds = \App\Models\MenuProduct::where('menu_id', $menu->id)
            ->whereNotNull('category_id')
            ->distinct()
            ->pluck('category_id')
            ->toArray();

        foreach ($categoryIds as $categoryId) {
            $category = Category::find($categoryId);
            if (!$category) continue;

            // Get products for this category in this menu, ordered by order field
            $menuProducts = \App\Models\MenuProduct::where('menu_id', $menu->id)
                ->where('category_id', $categoryId)
                ->with('product')
                ->orderBy('order', 'asc')
                ->get();

            $categoriesWithProducts[] = [
                'category' => $category,
                'products' => $menuProducts
            ];
        }

        // Also get products without category
        $productsWithoutCategory = \App\Models\MenuProduct::where('menu_id', $menu->id)
            ->whereNull('category_id')
            ->with('product')
            ->orderBy('order', 'asc')
            ->get();

        return view('dashboard.menus.categories', compact(
            'menu',
            'categoriesWithProducts',
            'productsWithoutCategory'
        ));
    }

    /**
     * Update product order within a menu
     */
    public function updateProductOrder(Request $request, $id)
    {
        try {
            $request->validate([
                'products' => 'required|array',
                'products.*.menu_product_id' => 'required|exists:menu_products,id',
                'products.*.order' => 'required|integer|min:0',
            ]);

            foreach ($request->input('products') as $productData) {
                \App\Models\MenuProduct::where('id', $productData['menu_product_id'])
                    ->where('menu_id', $id)
                    ->update(['order' => $productData['order']]);
            }

            return response()->json([
                'status' => 'success',
                'message' => __('admin.update_success')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.update_error'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}


