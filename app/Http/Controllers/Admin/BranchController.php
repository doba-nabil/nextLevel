<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\BranchDataTable;
use App\DataTables\BranchProductsDataTable;
use App\DataTables\CategoryDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BranchRequest;
use App\Models\BranchWorkingHour;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Services\BranchService;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct(private BranchService $branchService) {}


    public function index(BranchDataTable $dataTable)
    {
        return $dataTable->render('dashboard.branches.index');
    }

    public function create()
    {
        $countries = Location::where('type', 'country')->where('active', true)->get();
        return view('dashboard.branches.create',compact('countries'));
    }

    public function store(BranchRequest $request)
    {
        $this->branchService->create(
            $request->validated()
        );
        return redirect()->route('branches.index')->with('success', __('admin.save_success'));
    }

    public function show(Branch $branch)
    {
        $branch->load('location', 'workingHours');
        return view('dashboard.branches.show', compact('branch'));
    }

    public function edit($id)
    {
        $branch = $this->branchService->getById($id);
        $branch->load([
            'cities.parent.parent',
            'location.parent.parent'
        ]);
        $countries = Location::where('type', 'country')->where('active', true)->get();
        $workingHours  = BranchWorkingHour::where('branch_id', $id)->get();
        return view('dashboard.branches.edit', compact('branch', 'countries', 'workingHours'));
    }

    public function update(BranchRequest $request, $id)
    {
        $branch = $this->branchService->getById($id);
        $this->branchService->update($branch, $request->validated());
        return redirect()->route('branches.index')->with('success', __('admin.update_success'));
    }

    public function destroy($id)
    {
        try {
            $this->branchService->delete($id);
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

    public function checkCityAvailability(Request $request)
    {
        $cityIds = $request->input('city_ids', []);
        $branchId = $request->input('branch_id'); // For edit mode, exclude current branch

        if (empty($cityIds)) {
            return response()->json([
                'available' => true,
                'conflicts' => []
            ]);
        }

        $conflicts = [];

        foreach ($cityIds as $cityId) {
            $city = \App\Models\Location::find($cityId);
            $cityName = $city ? $city->getTranslation('name', app()->getLocale()) : '';

            $query = \App\Models\Branch::whereHas('cities', function($q) use ($cityId) {
                $q->where('locations.id', $cityId);
            });

            // Exclude current branch if editing
            if ($branchId) {
                $query->where('branches.id', '!=', $branchId);
            }

            $branch = $query->first();

            if ($branch) {
                $conflicts[] = [
                    'city_id' => $cityId,
                    'city_name' => $cityName,
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->getTranslation('name', app()->getLocale())
                ];
            }
        }

        return response()->json([
            'available' => empty($conflicts),
            'conflicts' => $conflicts
        ]);
    }

    /**
     * عرض منتجات الفرع
     */
    public function products($id, BranchProductsDataTable $dataTable)
    {
        $branch = $this->branchService->getById($id);

        // Pass branch ID to DataTable
        $dataTable->setBranchId($id);

        return $dataTable->render('dashboard.branches.products', compact('branch'));
    }

    /**
     * تغيير status المنتج في الفرع
     */
    public function toggleProductStatus($id, $productId, Request $request)
    {
        try {
            $branch = $this->branchService->getById($id);
            $product = Product::findOrFail($productId);

            // التحقق من وجود العلاقة
            $productBranch = ProductBranch::where('branch_id', $id)
                ->where('product_id', $productId)
                ->first();

            if (!$productBranch) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('admin.product_not_found_in_branch') ?? 'المنتج غير موجود في هذا الفرع'
                ], 404);
            }

            // تغيير status
            $newStatus = $productBranch->status === 'available' ? 'unavailable' : 'available';
            $productBranch->update(['status' => $newStatus]);

            return response()->json([
                'status' => 'success',
                'message' => __('admin.status_updated_successfully') ?? 'تم تحديث الحالة بنجاح',
                'new_status' => $newStatus,
                'status_label' => $newStatus === 'available'
                    ? (__('admin.available') ?? 'متاح')
                    : (__('admin.unavailable') ?? 'غير متاح')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.update_error') ?? 'حدث خطأ أثناء التحديث'
            ], 500);
        }
    }

}
