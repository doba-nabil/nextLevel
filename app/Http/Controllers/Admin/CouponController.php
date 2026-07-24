<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\CouponDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use App\Models\User;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(private CouponService $couponService) {}

    public function index(CouponDataTable $dataTable)
    {
        return $dataTable->render('dashboard.coupons.index');
    }

    public function create()
    {
        $users = User::where('is_admin', 0)->get();
        return view('dashboard.coupons.create', compact('users'));
    }

    public function store(CouponRequest $request)
    {
        $this->couponService->create($request->validated());
        return redirect()->route('coupons.index')->with('success', __('admin.save_success'));
    }

    public function edit(Coupon $coupon)
    {
        $users = User::where('is_admin', 0)->get();
        return view('dashboard.coupons.edit', compact('coupon', 'users'));
    }

    public function update(CouponRequest $request, Coupon $coupon)
    {
        $this->couponService->update($coupon, $request->validated());
        return redirect()->route('coupons.index')->with('success', __('admin.update_success'));
    }

    public function destroy($id)
    {
        try {
            $this->couponService->delete($id);
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
}
