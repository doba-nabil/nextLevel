<?php

namespace App\Http\Controllers\BranchPanel;

use App\Http\Controllers\Controller;
use App\Services\BranchDashboardService;

class DashboardController extends Controller
{
    public function __construct(private BranchDashboardService $dashboardService)
    {
    }

    public function index()
    {
        $branchId = auth('branch')->id();

        // Get basic stats for the branch
        $stats = $this->dashboardService->getStatistics($branchId);

        // Latest orders
        $latestOrders = $this->dashboardService->getLatestOrders($branchId);

        return view('branch.dashboard.index', array_merge($stats, [
            'latestOrders' => $latestOrders
        ]));
    }
}
