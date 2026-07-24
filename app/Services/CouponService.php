<?php
namespace App\Services;

use App\Models\Coupon;

class CouponService
{
    public function getAll()
    {
        return Coupon::with('user')->latest()->get();
    }

    public function getById($id)
    {
        return Coupon::findOrFail($id);
    }

    public function create(array $data): Coupon
    {
        return Coupon::create($data);
    }

    public function update(Coupon $coupon, array $data): Coupon
    {
        $coupon->update($data);
        return $coupon;
    }

    public function delete($id): bool
    {
        $coupon = Coupon::findOrFail($id);
        return $coupon->delete();
    }
}
