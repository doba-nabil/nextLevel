<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\ProductBranch;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class BranchMenuCopierController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        $title = __('admin.copy_branch_menu') ?? 'نسخ منيو الفرع';
        
        return view('admin.branch-menu-copier.index', compact('branches', 'title'));
    }

    public function copy(Request $request)
    {
        $request->validate([
            'source_branch_id' => 'required|exists:branches,id',
            'destination_branch_id' => 'required|exists:branches,id|different:source_branch_id',
            'types' => 'required|array|min:1',
            'types.*' => 'in:product,meal,box',
        ]);

        $sourceBranchId = $request->source_branch_id;
        $destinationBranchId = $request->destination_branch_id;
        $typesToCopy = $request->types;
        $deleteOld = $request->has('delete_old');

        DB::beginTransaction();
        try {
            if ($deleteOld) {
                // Delete from destination branch only the types selected
                ProductBranch::where('branch_id', $destinationBranchId)
                    ->whereHas('product', function ($query) use ($typesToCopy) {
                        $query->whereIn('product_type', $typesToCopy);
                    })->delete();
            }

            // Get source products matching selected types
            $sourceProducts = ProductBranch::where('branch_id', $sourceBranchId)
                ->whereHas('product', function ($query) use ($typesToCopy) {
                    $query->whereIn('product_type', $typesToCopy);
                })
                ->get();

            // Insert into destination branch
            foreach ($sourceProducts as $sourceProduct) {
                // Check if already exists to avoid duplicates (unless we just deleted them)
                $exists = ProductBranch::where('branch_id', $destinationBranchId)
                    ->where('product_id', $sourceProduct->product_id)
                    ->exists();

                if (!$exists) {
                    ProductBranch::create([
                        'branch_id' => $destinationBranchId,
                        'product_id' => $sourceProduct->product_id,
                        'status' => $sourceProduct->status,
                        'stock' => $sourceProduct->stock,
                        'max_order_quantity' => $sourceProduct->max_order_quantity,
                        'low_stock_threshold' => $sourceProduct->low_stock_threshold,
                        'low_stock_notified' => $sourceProduct->low_stock_notified,
                        'track_stock' => $sourceProduct->track_stock,
                    ]);
                }
            }

            DB::commit();

            // Return success
            return redirect()->back()->with('success', __('admin.copied_successfully') ?? 'تم النسخ بنجاح');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', __('admin.error') ?? 'حدث خطأ: ' . $e->getMessage());
        }
    }
}
