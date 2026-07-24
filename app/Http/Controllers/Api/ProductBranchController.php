<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductBranchController extends Controller
{
    /**
     * Link all products to all branches
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function linkAllProductsToAllBranches(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'status' => 'sometimes|string|in:available,unavailable', // Optional: default status
                'replace' => 'sometimes|boolean' // Optional: replace existing links or keep them
            ]);

            $status = $request->input('status', 'available');
            $replace = $request->input('replace', false); // Default: keep existing links

            // Get all products and branches
            $products = Product::all();
            $branches = Branch::all();

            if ($products->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No products found in database'
                ], 404);
            }

            if ($branches->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No branches found in database'
                ], 404);
            }

            $linked = 0;
            $skipped = 0;
            $errors = [];

            DB::beginTransaction();

            try {
                // Get all branch IDs
                $branchIds = $branches->pluck('id')->toArray();

                foreach ($products as $product) {
                    try {
                        // Get current branch IDs for this product
                        $currentBranchIds = $product->branches()->pluck('branches.id')->toArray();

                        if (!$replace) {
                            // Only add branches that don't exist
                            $newBranchIds = array_diff($branchIds, $currentBranchIds);

                            if (empty($newBranchIds)) {
                                $skipped += count($branchIds);
                                continue;
                            }

                            // Attach new branches with status
                            foreach ($newBranchIds as $branchId) {
                                $product->branches()->attach($branchId, ['status' => $status]);
                                $linked++;
                            }

                            $skipped += count($currentBranchIds);
                        } else {
                            // Replace all - sync all branches with status
                            $syncData = [];
                            foreach ($branchIds as $branchId) {
                                $syncData[$branchId] = ['status' => $status];
                            }

                            $product->branches()->sync($syncData);
                            $linked += count($branchIds);
                        }

                    } catch (\Exception $e) {
                        $errors[] = [
                            'product_id' => $product->id,
                            'error' => $e->getMessage()
                        ];

                        Log::error('Error linking product to branches', [
                            'product_id' => $product->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                DB::commit();

                Log::info('All products linked to all branches', [
                    'total_products' => $products->count(),
                    'total_branches' => $branches->count(),
                    'linked' => $linked,
                    'skipped' => $skipped,
                    'errors' => count($errors),
                    'status' => $status
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Linked {$linked} product-branch associations successfully" .
                                ($skipped > 0 ? ", {$skipped} skipped (already exist)" : "") .
                                (count($errors) > 0 ? ", " . count($errors) . " errors" : ""),
                    'summary' => [
                        'total_products' => $products->count(),
                        'total_branches' => $branches->count(),
                        'total_possible_links' => $products->count() * $branches->count(),
                        'linked' => $linked,
                        'skipped' => $skipped,
                        'errors' => count($errors),
                        'status' => $status
                    ],
                    'errors' => $errors
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error linking all products to all branches', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error linking products to branches: ' . $e->getMessage()
            ], 500);
        }
    }
}
