<?php

namespace App\Http\Controllers\BranchPanel;

use App\Http\Controllers\Controller;
use App\DataTables\BranchProductsDataTable;
use Illuminate\Http\Request;
use App\Services\BranchProductService;

class ProductController extends Controller
{
    public function __construct(private BranchProductService $productService)
    {
    }

    public function products(BranchProductsDataTable $dataTable)
    {
        $branchId = auth('branch')->id();
        $dataTable->setBranchId($branchId);
        
        request()->merge(['product_type' => 'product']);

        return $dataTable->render('branch.products.index', ['title' => __('admin.products')]);
    }

    public function meals(BranchProductsDataTable $dataTable)
    {
        $branchId = auth('branch')->id();
        $dataTable->setBranchId($branchId);
        
        request()->merge(['product_type' => 'meal']);

        return $dataTable->render('branch.products.index', ['title' => __('admin.meals')]);
    }

    public function boxes(BranchProductsDataTable $dataTable)
    {
        $branchId = auth('branch')->id();
        $dataTable->setBranchId($branchId);
        
        request()->merge(['product_type' => 'box']);

        return $dataTable->render('branch.products.index', ['title' => __('admin.boxes')]);
    }

    public function toggleProductStatus($productId, Request $request)
    {
        try {
            $branch = auth('branch')->user();
            $result = $this->productService->toggleStatus($branch, $productId);

            return response()->json([
                'status' => 'success',
                'message' => __('admin.status_updated_successfully') ?? 'تم تحديث الحالة بنجاح',
                'new_status' => $result['new_status'],
                'status_label' => $result['status_label']
            ]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 500;
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?: (__('admin.update_error') ?? 'حدث خطأ أثناء التحديث')
            ], $statusCode);
        }
    }

    public function updateProductSettings($productId, Request $request)
    {
        try {
            $branch = auth('branch')->user();
            
            $data = [
                'track_stock' => $request->has('track_stock') ? (bool)$request->input('track_stock') : false,
                'stock' => (int)$request->input('stock', 0),
                'max_order_quantity' => (int)$request->input('max_order_quantity', 0),
                'low_stock_threshold' => (int)$request->input('low_stock_threshold', 5),
            ];

            $this->productService->updateSettings($branch, $productId, $data);

            return response()->json([
                'status' => 'success',
                'message' => __('admin.update_success') ?? 'تم التحديث بنجاح'
            ]);
        } catch (\Exception $e) {
            $statusCode = in_array($e->getCode(), [404, 422]) ? $e->getCode() : 500;
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?: (__('admin.update_error') ?? 'حدث خطأ أثناء التحديث')
            ], $statusCode);
        }
    }
}
