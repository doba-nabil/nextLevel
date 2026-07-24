<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Location;
use App\Models\Branch;
use App\Services\SmsService;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Order Statistics
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        $processingOrders = Order::where('status', 'processing')->count();

        // Sales Statistics - This Month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        $thisMonthSales = Order::where('status', 'completed')
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->sum('total');
        
        // Last Month for comparison
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        $lastMonthSales = Order::where('status', 'completed')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('total');

        // Average Daily Sales (last 7 days)
        $last7Days = [];
        $last7DaysSales = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $daySales = Order::where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('total');
            $last7Days[] = $date->format('D');
            $last7DaysSales[] = (float) $daySales;
        }

        // This Year vs Last Year
        $thisYearStart = Carbon::now()->startOfYear();
        $thisYearSales = Order::where('status', 'completed')
            ->where('created_at', '>=', $thisYearStart)
            ->sum('total');

        $lastYearStart = Carbon::now()->subYear()->startOfYear();
        $lastYearEnd = Carbon::now()->subYear()->endOfYear();
        $lastYearSales = Order::where('status', 'completed')
            ->whereBetween('created_at', [$lastYearStart, $lastYearEnd])
            ->sum('total');

        // Sales growth percentage
        $salesGrowth = $lastMonthSales > 0 
            ? round((($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 2)
            : 0;

        // Last 7 Days Orders Count by Day
        $last7DaysOrdersCount = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayOrders = Order::whereDate('created_at', $date)->count();
            $last7DaysOrdersCount[] = $dayOrders;
        }

        // Support Tracker Data (Orders last 7 days)
        $supportTrackerData = [];
        $supportTrackerLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $supportTrackerLabels[] = $date->format('M d');
            $supportTrackerData[] = Order::whereDate('created_at', $date)->count();
        }

        // Weekly Earnings (Last 7 days - Monday to Sunday)
        $weeklyEarnings = [];
        $weeklyLabels = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];
        $startOfWeek = Carbon::now()->startOfWeek();
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dayEarnings = Order::where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('total');
            $weeklyEarnings[] = (float) $dayEarnings;
        }

        // Total Earning Chart Data (Last 8 months)
        $monthlyEarnings = [];
        $monthlyExpenses = [];
        $monthlyLabels = [];
        for ($i = 7; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $monthlyLabels[] = $month->format('M');
            $earnings = Order::where('status', 'completed')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total');
            $monthlyEarnings[] = (float) $earnings;
            // Expenses would come from another model if you have one, for now 0
            $monthlyExpenses[] = 0;
        }

        // Sales by Country
        $salesByCountry = [];
        $countries = Location::whereNull('parent_id')->get();
        foreach ($countries as $country) {
            $countrySales = Order::where('status', 'completed')
                ->whereHas('user', function($query) use ($country) {
                    $query->where('location_id', $country->id);
                })
                ->sum('total');
            
            $countryOrders = Order::where('status', 'completed')
                ->whereHas('user', function($query) use ($country) {
                    $query->where('location_id', $country->id);
                })
                ->count();
            
            $salesByCountry[] = [
                'name' => $country->name,
                'sales' => (float) $countrySales,
                'orders' => (int) $countryOrders
            ];
        }

        // Most Visited Products (Top 5) - Products with most orders
        // Using subquery to avoid GROUP BY issues with ONLY_FULL_GROUP_BY
        $mostVisitedProducts = DB::table('products')
            ->select('products.id', 'products.name', DB::raw('COUNT(DISTINCT order_items.order_id) as orders_count'))
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy('products.id', 'products.name')
            ->orderBy('orders_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($product) {
                $productModel = Product::find($product->id);
                return [
                    'name' => $productModel ? $productModel->name : [],
                    'orders' => (int) ($product->orders_count ?? 0)
                ];
            });

        // Most Sold Products (Top 5)
        $mostSoldProducts = DB::table('products')
            ->select(
                'products.id', 
                'products.name',
                DB::raw('COALESCE(SUM(CASE WHEN orders.status = "completed" THEN order_items.quantity ELSE 0 END), 0) as total_sold')
            )
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get()
            ->map(function($product) {
                $productModel = Product::find($product->id);
                return [
                    'name' => $productModel ? $productModel->name : [],
                    'sold' => (int) ($product->total_sold ?? 0)
                ];
            });

        // Support Tracker Percentage (Completed orders / Total orders)
        $supportTrackerPercentage = $totalOrders > 0 
            ? round(($completedOrders / $totalOrders) * 100, 1)
            : 0;

        // Average Daily Sales calculation
        $averageDailySales = count($last7DaysSales) > 0 
            ? round(array_sum($last7DaysSales) / count($last7DaysSales), 2)
            : 0;

        // Get SMS Balance (cached for 1 hour)
        $smsBalance = Cache::remember('sms_balance', 3600, function () {
            try {
                $smsService = new SmsService();
                $balanceResult = $smsService->checkBalance();
                
                if ($balanceResult['success']) {
                    $response = $balanceResult['response'];
                    $data = $balanceResult['data'];
                    
                    // Try to extract balance from response
                    // KWTSMS might return balance in different formats
                    $balance = null;
                    if (is_array($data) && isset($data['balance'])) {
                        $balance = $data['balance'];
                    } elseif (is_array($data) && isset($data['Balance'])) {
                        $balance = $data['Balance'];
                    } elseif (is_numeric($response)) {
                        $balance = (int) $response;
                    } elseif (preg_match('/(\d+\.?\d*)/', $response, $matches)) {
                        $balance = (int) $matches[1];
                    } else {
                        $balance = $response; // Return raw response if can't parse
                    }
                    
                    // Convert to integer if numeric
                    if (is_numeric($balance)) {
                        $balance = (int) $balance;
                    }
                    
                    return $balance;
                }
                
                return __('admin.sms_balance_error');
            } catch (\Exception $e) {
                \Log::error('SMS Balance Check Error in Dashboard', [
                    'error' => $e->getMessage()
                ]);
                return __('admin.sms_balance_error');
            }
        });

        return view('dashboard.home', compact(
            'totalOrders',
            'pendingOrders',
            'completedOrders',
            'cancelledOrders',
            'processingOrders',
            'thisMonthSales',
            'lastMonthSales',
            'salesGrowth',
            'last7DaysSales',
            'last7Days',
            'last7DaysOrdersCount',
            'thisYearSales',
            'lastYearSales',
            'supportTrackerData',
            'supportTrackerLabels',
            'supportTrackerPercentage',
            'weeklyEarnings',
            'weeklyLabels',
            'monthlyEarnings',
            'monthlyExpenses',
            'monthlyLabels',
            'salesByCountry',
            'mostVisitedProducts',
            'mostSoldProducts',
            'averageDailySales',
            'countries',
            'smsBalance'
        ));
    }

    /**
     * Refresh SMS Balance
     */
    public function refreshSmsBalance()
    {
        try {
            // Clear cache
            Cache::forget('sms_balance');
            
            // Get fresh balance
            $smsService = new SmsService();
            $balanceResult = $smsService->checkBalance();
            
            if ($balanceResult['success']) {
                $response = $balanceResult['response'];
                $data = $balanceResult['data'];
                
                // Try to extract balance from response
                $balance = null;
                if (is_array($data) && isset($data['balance'])) {
                    $balance = $data['balance'];
                } elseif (is_array($data) && isset($data['Balance'])) {
                    $balance = $data['Balance'];
                } elseif (is_numeric($response)) {
                    $balance = (int) $response;
                } elseif (preg_match('/(\d+\.?\d*)/', $response, $matches)) {
                    $balance = (int) $matches[1];
                } else {
                    $balance = $response;
                }
                
                // Convert to integer if numeric
                if (is_numeric($balance)) {
                    $balance = (int) $balance;
                }
                
                // Cache for 1 hour
                Cache::put('sms_balance', $balance, 3600);
                
                return response()->json([
                    'success' => true,
                    'balance' => $balance
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => __('admin.sms_balance_error')
            ], 400);
            
        } catch (\Exception $e) {
            \Log::error('SMS Balance Refresh Error', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => __('admin.sms_balance_error') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send test Firebase notification
     */
    public function testFirebaseNotification(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
        ]);

        try {
            $branch = Branch::findOrFail($request->branch_id);

            if (empty($branch->firebase)) {
                return response()->json([
                    'success' => false,
                    'message' => __('admin.branch_has_no_firebase_token')
                ], 400);
            }

            $lang = $branch->lang ?? 'ar';
            $messages = [
                'ar' => [
                    'title' => 'إشعار تجريبي 🔔',
                    'body' => 'هذا إشعار تجريبي من نظام run2diet',
                ],
                'en' => [
                    'title' => 'Test Notification 🔔',
                    'body' => 'This is a test notification from run2diet system',
                ],
            ];

            $notificationMessages = $messages[$lang] ?? $messages['ar'];

            $firebaseService = new FirebaseNotificationService();
            $result = $firebaseService->sendNotification(
                $branch->firebase,
                $notificationMessages['title'],
                $notificationMessages['body'],
                [
                    'type' => 'test',
                    'message' => 'This is a test notification from admin panel',
                    'branch_id' => (string) $branch->id,
                ]
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => __('admin.firebase_notification_sent_successfully'),
                    'message_id' => $result['message_id'] ?? null,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Firebase Test Notification Error', [
                'error' => $e->getMessage(),
                'branch_id' => $request->branch_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => __('admin.firebase_notification_error') . ': ' . $e->getMessage()
            ], 500);
        }
    }
}

