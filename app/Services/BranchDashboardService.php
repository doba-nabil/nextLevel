<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;

class BranchDashboardService
{
    /**
     * Get dashboard statistics for a specific branch.
     *
     * @param int $branchId
     * @return array
     */
    public function getStatistics(int $branchId): array
    {
        return [
            'totalOrders' => Order::where('branch_id', $branchId)->count(),
            'pendingOrders' => Order::where('branch_id', $branchId)->where('status', 'pending')->count(),
            'completedOrders' => Order::where('branch_id', $branchId)->where('status', 'completed')->count(),
        ];
    }

    /**
     * Get latest orders for a specific branch.
     *
     * @param int $branchId
     * @param int $limit
     * @return Collection
     */
    public function getLatestOrders(int $branchId, int $limit = 10): Collection
    {
        return Order::where('branch_id', $branchId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
