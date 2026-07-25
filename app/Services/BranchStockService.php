<?php

namespace App\Services;

use App\Jobs\SendLowStockAlertJob;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBranch;
use Illuminate\Support\Facades\DB;

class BranchStockService
{
    public function resolveBranchId(?int $branchId = null): ?int
    {
        if ($branchId) {
            return $branchId;
        }

        $menuType = session('menu_type', 'delivery');
        if ($menuType === 'pickup') {
            return session('pickup_branch_id') ? (int) session('pickup_branch_id') : null;
        }

        $userLocation = session('user_location');
        $cityId = $userLocation['city_id'] ?? null;
        if (!$cityId) {
            return null;
        }

        $branch = Branch::where('active', 1)
            ->whereHas('cities', function ($q) use ($cityId) {
                $q->where('locations.id', $cityId);
            })
            ->first();

        return $branch?->id;
    }

    public function getDetails(Product $product, ?int $branchId = null): array
    {
        $branchId = $this->resolveBranchId($branchId);

        if (!$product->supportsBranchStock()) {
            return [
                'track_stock' => false,
                'stock' => 99999,
                'max_order_quantity' => 0,
                'low_stock_threshold' => 0,
                'is_out_of_stock' => false,
                'is_available_in_branch' => true,
                'branch_id' => $branchId,
                'max_allowed_quantity' => 99999,
            ];
        }

        if (!$branchId) {
            return [
                'track_stock' => false,
                'stock' => 0,
                'max_order_quantity' => 0,
                'low_stock_threshold' => 0,
                'is_out_of_stock' => true,
                'is_available_in_branch' => false,
                'branch_id' => null,
                'max_allowed_quantity' => 0,
            ];
        }

        $branchPivot = DB::table('product_branches')
            ->where('branch_id', $branchId)
            ->where('product_id', $product->id)
            ->first();

        if (!$branchPivot) {
            return [
                'track_stock' => false,
                'stock' => 0,
                'max_order_quantity' => 0,
                'low_stock_threshold' => 0,
                'is_out_of_stock' => true,
                'is_available_in_branch' => false,
                'branch_id' => $branchId,
                'max_allowed_quantity' => 0,
            ];
        }

        $isAvailable = ($branchPivot->status ?? 'available') === 'available';
        $trackStock = (bool) ($branchPivot->track_stock ?? false);
        $stock = $trackStock ? (int) ($branchPivot->stock ?? 0) : 99999;
        $maxOrderQuantity = (int) ($branchPivot->max_order_quantity ?? 0);
        $lowStockThreshold = (int) ($branchPivot->low_stock_threshold ?? 5);

        $isOutOfStock = !$isAvailable || ($trackStock && $stock <= 0);

        $maxAllowed = 99999;
        if ($trackStock) {
            $maxAllowed = min($maxAllowed, max(0, $stock));
        }
        if ($maxOrderQuantity > 0) {
            $maxAllowed = min($maxAllowed, $maxOrderQuantity);
        }
        if ($isOutOfStock) {
            $maxAllowed = 0;
        }

        return [
            'track_stock' => $trackStock,
            'stock' => $stock,
            'max_order_quantity' => $maxOrderQuantity,
            'low_stock_threshold' => $lowStockThreshold,
            'is_out_of_stock' => $isOutOfStock,
            'is_available_in_branch' => $isAvailable,
            'branch_id' => $branchId,
            'max_allowed_quantity' => $maxAllowed,
        ];
    }

    public function validateCartQuantity(Product $product, int $quantity, ?int $branchId = null): ?string
    {
        if (!$product->supportsBranchStock()) {
            return null;
        }

        $details = $this->getDetails($product, $branchId);

        if (!$details['is_available_in_branch'] || $details['is_out_of_stock']) {
            return __('website.out_of_stock');
        }

        if ($details['track_stock'] && $quantity > $details['stock']) {
            return __('website.only_x_available', ['count' => $details['stock']]);
        }

        if ($details['max_order_quantity'] > 0 && $quantity > $details['max_order_quantity']) {
            return __('website.max_quantity_reached', ['max_quantity' => $details['max_order_quantity']]);
        }

        return null;
    }

    /**
     * Validate all items in session cart for the resolved branch.
     */
    public function validateSessionCart(?int $branchId = null): ?string
    {
        $branchId = $this->resolveBranchId($branchId);
        $cart = session('cart', []);

        foreach ($cart as $item) {
            $product = Product::withoutGlobalScope('city_availability')
                ->where('active', true)
                ->find($item['product_id'] ?? null);

            if (!$product) {
                continue;
            }

            $error = $this->validateCartQuantity($product, (int) ($item['quantity'] ?? 1), $branchId);
            if ($error) {
                $productName = $product->getTranslation('name', app()->getLocale())
                    ?: ($product->name['ar'] ?? '');

                return $productName ? "{$productName}: {$error}" : $error;
            }
        }

        return null;
    }

    public function validateOrderItems(Order $order): ?string
    {
        if (!$order->branch_id) {
            return null;
        }

        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            $product = $item->product;
            if (!$product) {
                continue;
            }

            $error = $this->validateCartQuantity($product, (int) $item->quantity, $order->branch_id);
            if ($error) {
                $productName = $product->getTranslation('name', app()->getLocale())
                    ?: ($product->name['ar'] ?? '');
                return $productName ? "{$productName}: {$error}" : $error;
            }
        }

        return null;
    }

    public function deductForOrder(Order $order): void
    {
        if (!$order->branch_id) {
            return;
        }

        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            $product = $item->product;
            if (!$product || !$product->supportsBranchStock()) {
                continue;
            }

            $productBranch = ProductBranch::where('branch_id', $order->branch_id)
                ->where('product_id', $product->id)
                ->first();

            if (!$productBranch || !$productBranch->track_stock) {
                continue;
            }

            $productBranch->decrement('stock', $item->quantity);
            $productBranch->refresh();

            if (
                $productBranch->stock <= $productBranch->low_stock_threshold
                && !$productBranch->low_stock_notified
            ) {
                SendLowStockAlertJob::dispatch($productBranch);
                $productBranch->update(['low_stock_notified' => true]);
            }
        }
    }
}
