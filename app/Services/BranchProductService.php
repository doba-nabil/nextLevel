<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranch;
use Illuminate\Support\Facades\Cache;

class BranchProductService
{
    /**
     * Toggle the status of a product in a specific branch.
     *
     * @param Branch $branch
     * @param int $productId
     * @return array
     * @throws \Exception
     */
    public function toggleStatus(Branch $branch, int $productId): array
    {
        $productBranch = ProductBranch::where('branch_id', $branch->id)
            ->where('product_id', $productId)
            ->first();

        if (!$productBranch) {
            throw new \Exception(__('admin.product_not_found_in_branch') ?? 'المنتج غير موجود في هذا الفرع', 404);
        }

        $newStatus = $productBranch->status === 'available' ? 'unavailable' : 'available';
        $productBranch->update(['status' => $newStatus]);

        $this->clearBranchCaches($branch);

        return [
            'new_status' => $newStatus,
            'status_label' => $newStatus === 'available'
                ? (__('admin.available') ?? 'متاح')
                : (__('admin.unavailable') ?? 'غير متاح')
        ];
    }

    /**
     * Update product settings (stock, thresholds) in a specific branch.
     *
     * @param Branch $branch
     * @param int $productId
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function updateSettings(Branch $branch, int $productId, array $data): void
    {
        $product = Product::findOrFail($productId);

        $productBranch = ProductBranch::where('branch_id', $branch->id)
            ->where('product_id', $productId)
            ->first();

        if (!$productBranch) {
            throw new \Exception(__('admin.product_not_found_in_branch') ?? 'المنتج غير موجود في هذا الفرع', 404);
        }

        if (!$product->supportsBranchStock()) {
            throw new \Exception(__('admin.stock_settings_products_only') ?? 'إعدادات المخزون متاحة للمنتجات فقط', 422);
        }

        $productBranch->update([
            'track_stock' => $data['track_stock'] ?? false,
            'stock' => $data['stock'] ?? 0,
            'max_order_quantity' => $data['max_order_quantity'] ?? 0,
            'low_stock_threshold' => $data['low_stock_threshold'] ?? 5,
        ]);

        if ($productBranch->stock > $productBranch->low_stock_threshold) {
            $productBranch->update(['low_stock_notified' => false]);
        }

        $this->clearBranchCaches($branch);
    }

    /**
     * Clear caches related to the branch's cities.
     *
     * @param Branch $branch
     * @return void
     */
    protected function clearBranchCaches(Branch $branch): void
    {
        $cityIds = $branch->cities->pluck('id')->toArray();
        $menuTypes = ['delivery', 'pickup'];
        $productTypes = [0, 1]; // 0 = product, 1 = box

        foreach ($cityIds as $cityId) {
            foreach ($menuTypes as $mt) {
                foreach ($productTypes as $pt) {
                    Cache::forget("categories_{$mt}_{$pt}_city_{$cityId}");
                    Cache::forget("categories_{$mt}_{$pt}_city_");
                }
                Cache::forget("home_products_{$mt}_city_{$cityId}");
                Cache::forget("home_products_{$mt}_city_");
                Cache::forget("categories_home_{$mt}_product_v2_city_{$cityId}");
                Cache::forget("categories_home_{$mt}_product_v2_city_");
                Cache::forget("menus_active_{$mt}");
                Cache::forget("menus_active_{$mt}_city_");
                Cache::forget("menus_active_{$mt}_city_{$cityId}");
            }
        }
        Cache::forget('burger_category');
    }
}
