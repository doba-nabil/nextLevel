<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Branch;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportsExport;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'date_range' => $request->input('date_range', 'month'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'status' => $request->input('status'),
            'payment_method' => $request->input('payment_method'),
            'product_id' => $request->input('product_id'),
            'category_id' => $request->input('category_id'),
            'user_id' => $request->input('user_id'),
            'branch_id' => $request->input('branch_id'),
            'city_id' => $request->input('city_id'),
            'state_id' => $request->input('state_id'),
            'country_id' => $request->input('country_id'),
        ];

        // Get date range
        $dateRange = $this->getDateRange($filters['date_range'], $filters['start_date'], $filters['end_date']);

        // Base query
        $ordersQuery = Order::query()
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->with(['user', 'branch', 'items.product.category']);

        // Apply filters
        if ($filters['status']) {
            $ordersQuery->where('status', $filters['status']);
        }

        if ($filters['payment_method']) {
            $ordersQuery->where('payment_method', $filters['payment_method']);
        }

        if ($filters['user_id']) {
            $ordersQuery->where('user_id', $filters['user_id']);
        }

        if ($filters['branch_id']) {
            $ordersQuery->where('branch_id', $filters['branch_id']);
        }

        // Location filters
        if ($filters['city_id'] || $filters['state_id'] || $filters['country_id']) {
            $ordersQuery->whereHas('user', function($q) use ($filters) {
                if ($filters['city_id']) {
                    $q->where('location_id', $filters['city_id']);
                } elseif ($filters['state_id']) {
                    $q->whereHas('city', function($cityQ) use ($filters) {
                        $cityQ->where('parent_id', $filters['state_id']);
                    });
                } elseif ($filters['country_id']) {
                    $q->whereHas('city', function($cityQ) use ($filters) {
                        $cityQ->whereHas('parent', function($stateQ) use ($filters) {
                            $stateQ->where('parent_id', $filters['country_id']);
                        });
                    });
                }
            });
        }

        $orders = $ordersQuery->get();

        // Calculate statistics
        $stats = $this->calculateStats($orders, $filters);

        // Get filter options
        $filterOptions = $this->getFilterOptions();

        return view('dashboard.reports.index', compact('stats', 'filters', 'filterOptions', 'dateRange'));
    }

    public function bestSellingProducts(Request $request)
    {
        $filters = [
            'date_range' => $request->input('date_range', 'month'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'status' => $request->input('status'),
            'category_id' => $request->input('category_id'),
            'branch_id' => $request->input('branch_id'),
            'limit' => $request->input('limit', 20),
        ];

        $dateRange = $this->getDateRange($filters['date_range'], $filters['start_date'], $filters['end_date']);

        $query = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
            ->where('orders.payment_status', 'paid');

        if ($filters['status']) {
            $query->where('orders.status', $filters['status']);
        }

        if ($filters['category_id']) {
            $query->where('products.category_id', $filters['category_id']);
        }

        if ($filters['branch_id']) {
            $query->where('orders.branch_id', $filters['branch_id']);
        }

        $bestSelling = $query
            ->select(
                'products.id',
                'products.name',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total) as total_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->groupBy('products.id', 'products.name', 'categories.name')
            ->orderBy('total_quantity', 'desc')
            ->limit($filters['limit'])
            ->get()
            ->map(function($product) {
                // Handle translatable names
                if (is_string($product->name)) {
                    $decoded = json_decode($product->name, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $product->name = $decoded;
                    }
                }
                if (is_string($product->category_name)) {
                    $decoded = json_decode($product->category_name, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $product->category_name = $decoded;
                    }
                }
                return $product;
            });

        $filterOptions = $this->getFilterOptions();

        return view('dashboard.reports.best-selling', compact('bestSelling', 'filters', 'filterOptions', 'dateRange'));
    }

    public function bestBranches(Request $request)
    {
        $filters = [
            'date_range' => $request->input('date_range', 'month'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'status' => $request->input('status'),
        ];

        $dateRange = $this->getDateRange($filters['date_range'], $filters['start_date'], $filters['end_date']);

        $query = Order::query()
            ->join('branches', 'orders.branch_id', '=', 'branches.id')
            ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
            ->where('orders.payment_status', 'paid');

        if ($filters['status']) {
            $query->where('orders.status', $filters['status']);
        }

        $bestBranches = $query
            ->select(
                'branches.id',
                'branches.name',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(orders.total) as total_revenue'),
                DB::raw('AVG(orders.total) as avg_order_value')
            )
            ->groupBy('branches.id', 'branches.name')
            ->orderBy('total_revenue', 'desc')
            ->get()
            ->map(function($branch) {
                // Handle translatable name
                if (is_string($branch->name)) {
                    $decoded = json_decode($branch->name, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $branch->name = $decoded;
                    }
                }
                return $branch;
            });

        $filterOptions = $this->getFilterOptions();

        return view('dashboard.reports.best-branches', compact('bestBranches', 'filters', 'filterOptions', 'dateRange'));
    }

    public function paymentMethods(Request $request)
    {
        $filters = [
            'date_range' => $request->input('date_range', 'month'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'status' => $request->input('status'),
        ];

        $dateRange = $this->getDateRange($filters['date_range'], $filters['start_date'], $filters['end_date']);

        $query = Order::query()
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('payment_status', 'paid');

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        $paymentMethods = $query
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->groupBy('payment_method')
            ->orderBy('count', 'desc')
            ->get();

        $filterOptions = $this->getFilterOptions();

        return view('dashboard.reports.payment-methods', compact('paymentMethods', 'filters', 'filterOptions', 'dateRange'));
    }

    private function getDateRange($range, $startDate = null, $endDate = null)
    {
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
            case 'week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek()
                ];
            case 'month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
            case 'year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear()
                ];
            case 'custom':
                return [
                    'start' => $startDate ? Carbon::parse($startDate)->startOfDay() : $now->copy()->startOfMonth(),
                    'end' => $endDate ? Carbon::parse($endDate)->endOfDay() : $now->copy()->endOfMonth()
                ];
            default:
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
        }
    }

    private function calculateStats($orders, $filters)
    {
        $stats = [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->where('payment_status', 'paid')->sum('total'),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'processing_orders' => $orders->where('status', 'processing')->count(),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
            'avg_order_value' => $orders->where('payment_status', 'paid')->avg('total') ?? 0,
        ];

        // Product-specific stats if product filter is applied
        if ($filters['product_id']) {
            $productOrders = $orders->filter(function($order) use ($filters) {
                return $order->items->contains('product_id', $filters['product_id']);
            });
            $stats['product_orders'] = $productOrders->count();
            $stats['product_revenue'] = $productOrders->where('payment_status', 'paid')->sum('total');
        }

        // Category-specific stats if category filter is applied
        if ($filters['category_id']) {
            $categoryOrders = $orders->filter(function($order) use ($filters) {
                return $order->items->contains(function($item) use ($filters) {
                    return $item->product && $item->product->category_id == $filters['category_id'];
                });
            });
            $stats['category_orders'] = $categoryOrders->count();
            $stats['category_revenue'] = $categoryOrders->where('payment_status', 'paid')->sum('total');
        }

        return $stats;
    }

    private function getFilterOptions()
    {
        return [
            'statuses' => ['pending', 'processing', 'completed', 'cancelled'],
            'payment_methods' => ['wallet', 'bookeey', 'mixed'],
            'products' => Product::where('is_active', 1)->get(),
            'categories' => Category::where('active', 1)->get(),
            'users' => User::where('is_admin', 0)->limit(100)->get(),
            'branches' => Branch::where('active', 1)->get(),
            'countries' => Location::countries()->where('active', 1)->get(),
            'states' => Location::states()->where('active', 1)->get(),
            'cities' => Location::cities()->where('active', 1)->get(),
        ];
    }

    /**
     * Export reports to Excel
     */
    public function exportExcel(Request $request, $type = 'overview')
    {
        $filters = $this->getFiltersFromRequest($request);
        $dateRange = $this->getDateRange($filters['date_range'], $filters['start_date'], $filters['end_date']);

        $data = $this->getExportData($type, $filters, $dateRange);
        $filename = $this->getExportFilename($type, $dateRange);

        return Excel::download(new ReportsExport($data, $type), $filename . '.xlsx');
    }

    /**
     * Export reports to CSV
     */
    public function exportCsv(Request $request, $type = 'overview')
    {
        $filters = $this->getFiltersFromRequest($request);
        $dateRange = $this->getDateRange($filters['date_range'], $filters['start_date'], $filters['end_date']);

        $data = $this->getExportData($type, $filters, $dateRange);
        $filename = $this->getExportFilename($type, $dateRange);

        return Excel::download(new ReportsExport($data, $type), $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * Export reports to PDF
     */
    public function exportPdf(Request $request, $type = 'overview')
    {
        $filters = $this->getFiltersFromRequest($request);
        $dateRange = $this->getDateRange($filters['date_range'], $filters['start_date'], $filters['end_date']);

        $data = $this->getExportData($type, $filters, $dateRange);
        $filename = $this->getExportFilename($type, $dateRange);

        try {
            // Try using Snappy first
            $view = 'dashboard.reports.exports.' . $type;
            $html = view($view, compact('data', 'filters', 'dateRange'))->render();

            return SnappyPdf::loadHTML($html)
                ->setOption('page-size', 'A4')
                ->setOption('orientation', 'landscape')
                ->setOption('margin-top', 10)
                ->setOption('margin-bottom', 10)
                ->setOption('margin-left', 10)
                ->setOption('margin-right', 10)
                ->download($filename . '.pdf');
        } catch (\Exception $e) {
            Log::error('PDF Export Error', [
                'error' => $e->getMessage(),
                'type' => $type
            ]);

            // Fallback: Try Excel PDF export
            try {
                return Excel::download(new ReportsExport($data, $type), $filename . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
            } catch (\Exception $excelException) {
                // If both fail, return error message
                return redirect()->back()->with('error', __('admin.pdf_export_failed') . ': ' . $e->getMessage() . '. ' . __('admin.please_install_wkhtmltopdf'));
            }
        }
    }

    private function getFiltersFromRequest(Request $request)
    {
        return [
            'date_range' => $request->input('date_range', 'month'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'status' => $request->input('status'),
            'payment_method' => $request->input('payment_method'),
            'product_id' => $request->input('product_id'),
            'category_id' => $request->input('category_id'),
            'user_id' => $request->input('user_id'),
            'branch_id' => $request->input('branch_id'),
            'city_id' => $request->input('city_id'),
            'state_id' => $request->input('state_id'),
            'country_id' => $request->input('country_id'),
        ];
    }

    private function getExportData($type, $filters, $dateRange)
    {
        switch ($type) {
            case 'best-selling':
                return $this->getBestSellingData($filters, $dateRange);
            case 'best-branches':
                return $this->getBestBranchesData($filters, $dateRange);
            case 'payment-methods':
                return $this->getPaymentMethodsData($filters, $dateRange);
            case 'overview':
            default:
                return $this->getOverviewData($filters, $dateRange);
        }
    }

    private function getBestSellingData($filters, $dateRange)
    {
        $query = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
            ->where('orders.payment_status', 'paid');

        if ($filters['status']) {
            $query->where('orders.status', $filters['status']);
        }
        if ($filters['category_id']) {
            $query->where('products.category_id', $filters['category_id']);
        }
        if ($filters['branch_id']) {
            $query->where('orders.branch_id', $filters['branch_id']);
        }

        return $query
            ->select(
                'products.id',
                'products.name',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total) as total_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->groupBy('products.id', 'products.name', 'categories.name')
            ->orderBy('total_quantity', 'desc')
            ->get()
            ->map(function($item) {
                // Handle translatable fields
                $name = $item->name;
                if (is_string($name)) {
                    $decoded = json_decode($name, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $name = $decoded;
                    }
                }
                $name = is_array($name) ? ($name[app()->getLocale()] ?? $name['ar'] ?? $name['en'] ?? '') : $name;
                
                $category = $item->category_name;
                if (is_string($category)) {
                    $decoded = json_decode($category, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $category = $decoded;
                    }
                }
                $category = is_array($category) ? ($category[app()->getLocale()] ?? $category['ar'] ?? $category['en'] ?? '-') : ($category ?? '-');
                
                return [
                    'product' => $name,
                    'category' => $category,
                    'total_quantity' => $item->total_quantity,
                    'total_revenue' => number_format($item->total_revenue, 3),
                    'order_count' => $item->order_count,
                ];
            });
    }

    private function getBestBranchesData($filters, $dateRange)
    {
        $query = Order::query()
            ->join('branches', 'orders.branch_id', '=', 'branches.id')
            ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
            ->where('orders.payment_status', 'paid');

        if ($filters['status']) {
            $query->where('orders.status', $filters['status']);
        }

        return $query
            ->select(
                'branches.id',
                'branches.name',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(orders.total) as total_revenue'),
                DB::raw('AVG(orders.total) as avg_order_value')
            )
            ->groupBy('branches.id', 'branches.name')
            ->orderBy('total_revenue', 'desc')
            ->get()
            ->map(function($item) {
                // Handle translatable fields
                $name = $item->name;
                if (is_string($name)) {
                    $decoded = json_decode($name, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $name = $decoded;
                    }
                }
                $name = is_array($name) ? ($name[app()->getLocale()] ?? $name['ar'] ?? $name['en'] ?? '') : $name;
                
                return [
                    'branch' => $name,
                    'total_orders' => $item->total_orders,
                    'total_revenue' => number_format($item->total_revenue, 3),
                    'avg_order_value' => number_format($item->avg_order_value, 3),
                ];
            });
    }

    private function getPaymentMethodsData($filters, $dateRange)
    {
        $query = Order::query()
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('payment_status', 'paid');

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        $paymentMethods = $query
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->groupBy('payment_method')
            ->orderBy('count', 'desc')
            ->get();

        $totalRevenue = $paymentMethods->sum('total_revenue');

        return $paymentMethods->map(function($item) use ($totalRevenue) {
            return [
                'payment_method' => ucfirst($item->payment_method),
                'count' => $item->count,
                'total_revenue' => number_format($item->total_revenue, 3),
                'percentage' => $totalRevenue > 0 ? number_format(($item->total_revenue / $totalRevenue) * 100, 2) : 0,
            ];
        });
    }

    private function getOverviewData($filters, $dateRange)
    {
        $ordersQuery = Order::query()
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->with(['user', 'branch', 'items.product.category']);

        if ($filters['status']) {
            $ordersQuery->where('status', $filters['status']);
        }
        if ($filters['payment_method']) {
            $ordersQuery->where('payment_method', $filters['payment_method']);
        }
        if ($filters['user_id']) {
            $ordersQuery->where('user_id', $filters['user_id']);
        }
        if ($filters['branch_id']) {
            $ordersQuery->where('branch_id', $filters['branch_id']);
        }

        $orders = $ordersQuery->get();

        return $orders->map(function($order) {
            $customerName = $order->user ? $order->user->name : $order->guest_name;
            $branchName = $order->branch ? (is_array($order->branch->name) ? ($order->branch->name[app()->getLocale()] ?? $order->branch->name['ar'] ?? $order->branch->name['en'] ?? '') : $order->branch->name) : '-';
            
            return [
                'order_number' => $order->order_number,
                'customer' => $customerName,
                'branch' => $branchName,
                'status' => __('admin.' . $order->status),
                'payment_method' => $order->payment_method,
                'total' => number_format($order->total, 3),
                'date' => $order->created_at->format('Y-m-d H:i'),
            ];
        });
    }

    private function getExportFilename($type, $dateRange)
    {
        $typeNames = [
            'overview' => __('admin.reports_overview'),
            'best-selling' => __('admin.best_selling_products'),
            'best-branches' => __('admin.best_branches'),
            'payment-methods' => __('admin.payment_methods_report'),
        ];

        $typeName = $typeNames[$type] ?? 'report';
        $dateStr = $dateRange['start']->format('Y-m-d') . '_to_' . $dateRange['end']->format('Y-m-d');
        
        return $typeName . '_' . $dateStr;
    }
}
