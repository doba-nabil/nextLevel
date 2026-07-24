@extends('dashboard.layout.master')
@section('title', __('admin.home'))
@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row g-6">
  <!-- SMS Balance -->
  <div class="col-xl-12 col-sm-12">
    <div class="card h-100">
        <div class="card-header pb-0">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 card-title">{{ __('admin.sms_balance') }}</h5>
                <button type="button" class="btn btn-sm btn-label-secondary" onclick="refreshSmsBalance()" title="{{ __('admin.refresh_sms_balance') }}">
                    <i class="icon-base ti tabler-refresh"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="avatar">
                    <div class="avatar-initial bg-label-primary rounded">
                        <i class="icon-base ti tabler-message-2"></i>
                    </div>
                </div>
                            <div class="ms-3">
                                <h4 class="mb-0" id="sms-balance-display">
                                    @if(is_numeric($smsBalance))
                                        {{ number_format((int)$smsBalance, 0) }}
                                    @else
                                        {{ $smsBalance }}
                                    @endif
                                </h4>
                                <small class="text-body-secondary">{{ __('admin.sms_balance') }}</small>
                            </div>
            </div>
        </div>
    </div>
</div>
<!--/ SMS Balance -->

            <!-- {{ __('admin.website_analytics') }} -->
            <div class="col-xl-6 col">
                <div
                    class="swiper-container swiper-container-horizontal swiper swiper-card-advance-bg"
                    id="swiper-with-pagination-cards" style="height: 100%;">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide" style="height: 100%;">
                            <div class="row">
                                <div class="col-12">
                                </div>
                                <div class="row">
                                    <div class="col-lg-7 col-md-9 col-12 order-2 order-md-1 pt-md-9">
                                        <h6 class="text-white mt-0 mt-md-3 mb-4">{{ __('admin.orders') }}</h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <ul class="list-unstyled mb-0">
                                                    <li class="d-flex mb-4 align-items-center">
                                                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">0</p>
                                                        <p class="mb-0">{{ __('admin.perchases') }}</p>
                                                    </li>
                                                    <li class="d-flex align-items-center">
                                                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">0</p>
                                                        <p class="mb-0">{{ __('admin.pending') }}</p>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-6">
                                                <ul class="list-unstyled mb-0">
                                                    <li class="d-flex mb-4 align-items-center">
                                                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">0</p>
                                                        <p class="mb-0">{{ __('admin.canceled') }}</p>
                                                    </li>
                                                    <li class="d-flex align-items-center">
                                                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">0</p>
                                                        <p class="mb-0">{{ __('admin.completed') }}</p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-md-3 col-12 order-1 order-md-2 my-4 my-md-0 text-center">
                                        <img
                                            src="{{ asset('dashboard') }}/assets/img/illustrations/cart.png"
                                            alt="{{ __('admin.website_analytics') }}"
                                            height="150"
                                            class="card-website-analytics-img" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
            <!--/ {{ __('admin.website_analytics') }} -->

            <!-- Average Daily Sales -->
            <div class="col-xl-3 col-sm-6">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <h5 class="mb-3 card-title">{{ __('admin.Average Daily Sales') }}</h5>
                        <p class="mb-0 text-body">{{ __('admin.Total Sales This Month') }}</p>
                        <h4 class="mb-0">{{ number_format($thisMonthSales, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</h4>
                    </div>
                    <div class="card-body px-0">
                        <div id="averageDailySales"></div>
                    </div>
                </div>
            </div>
            <!--/ Average Daily Sales -->

            <!-- Sales Overview -->
            <div class="col-xl-3 col-sm-6">
                <div class="card h-100">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <p class="mb-0 text-body">{{ __('admin.sales_overview') }}</p>
                            <p class="card-text fw-medium {{ $salesGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $salesGrowth >= 0 ? '+' : '' }}{{ number_format($salesGrowth, 1) }}%
                            </p>
                        </div>
                        <h4 class="card-title mb-1">{{ number_format($thisMonthSales, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="d-flex gap-2 align-items-center mb-2">
                            <span class="badge bg-label-info p-1 rounded"
                            ><i class="icon-base ti tabler-shopping-cart icon-sm"></i
                                ></span>
                                    <p class="mb-0">{{__('admin.perchases')}}</p>
                                </div>
                                @php
                                    $purchasesPercentage = $totalOrders > 0 ? round(($totalOrders / ($totalOrders + \App\Models\User::count())) * 100, 1) : 0;
                                    $visitsPercentage = 100 - $purchasesPercentage;
                                    $purchasesProgress = min($purchasesPercentage, 100);
                                    $visitsProgress = min($visitsPercentage, 100);
                                @endphp
                                <h5 class="mb-0 pt-1">{{ $purchasesPercentage }}%</h5>
                                <small class="text-body-secondary">{{ $totalOrders }}</small>
                            </div>
                            <div class="col-4">
                                <div class="divider divider-vertical">
                                    <div class="divider-text">
                                        <span class="badge-divider-bg bg-label-secondary">VS</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="d-flex gap-2 justify-content-end align-items-center mb-2">
                                    <p class="mb-0">{{ __('admin.visits') }}</p>
                                    <span class="badge bg-label-primary p-1 rounded"
                                    ><i class="icon-base ti tabler-link icon-sm"></i
                                        ></span>
                                </div>
                                <h5 class="mb-0 pt-1">{{ $visitsPercentage }}%</h5>
                                <small class="text-body-secondary">{{ \App\Models\User::count() }}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mt-6">
                            <div class="progress w-100" style="height: 10px">
                                <div
                                    class="progress-bar bg-info"
                                    style="width: {{ $purchasesProgress }}%"
                                    role="progressbar"
                                    aria-valuenow="{{ $purchasesProgress }}"
                                    aria-valuemin="0"
                                    aria-valuemax="100"></div>
                                <div
                                    class="progress-bar bg-primary"
                                    role="progressbar"
                                    style="width: {{ $visitsProgress }}%"
                                    aria-valuenow="{{ $visitsProgress }}"
                                    aria-valuemin="0"
                                    aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ Sales Overview -->

            <!-- Support Tracker -->
            <div class="col-12 col-md-12">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="mb-1">{{ __('admin.orders') }}</h5>
                            <p class="card-subtitle">{{ __('admin.last_7_days') }}</p>
                        </div>
                    </div>
                    <div class="card-body row">
                        <div class="col-12 col-sm-4">
                            <div class="mt-lg-4 mt-lg-2 mb-lg-6 mb-2">
                                <h2 class="mb-0">{{ $totalOrders }}</h2>
                                <p class="mb-0">{{ __('admin.total_orders') }}</p>
                            </div>
                            <ul class="p-0 m-0">
                                <li class="d-flex gap-4 align-items-center mb-lg-3 pb-1">
                                    <div class="badge rounded bg-label-primary p-1_5">
                                        <i class="icon-base ti tabler-ticket icon-md"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-nowrap">{{ __('admin.pending_orders') }}</h6>
                                        <small class="text-body-secondary">{{ $pendingOrders }}</small>
                                    </div>
                                </li>
                                <li class="d-flex gap-4 align-items-center mb-lg-3 pb-1">
                                    <div class="badge rounded bg-label-info p-1_5">
                                        <i class="icon-base ti tabler-circle-check icon-md"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-nowrap">{{ __('admin.canceled_orders') }}</h6>
                                        <small class="text-body-secondary">{{ $cancelledOrders }}</small>
                                    </div>
                                </li>
                                <li class="d-flex gap-4 align-items-center pb-1">
                                    <div class="badge rounded bg-label-warning p-1_5">
                                        <i class="icon-base ti tabler-clock icon-md"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-nowrap">{{ __('admin.completed_orders') }}</h6>
                                        <small class="text-body-secondary">{{ $completedOrders }}</small>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="col-12 col-md-8">
                            <div id="supportTracker"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ Support Tracker -->

            <!-- Sales By Country -->
            <div class="col-xxl-4 col-md-6 order-1 order-xl-0">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="mb-1">{{ __('admin.sales_by_countries') }}</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="p-0 m-0">
                            @foreach($salesByCountry as $countryData)
                            <li class="d-flex align-items-center mb-4">
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-0 me-1">{{ $countryData['orders'] }}</h6>
                                        </div>
                                        <small class="text-body">{{ $countryData['name'] }}</small>
                                    </div>
                                    <div class="user-progress">
                                        <p class="text-success fw-medium mb-0 d-flex align-items-center gap-1">
                                            {{ number_format($countryData['sales'], 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}
                                        </p>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <!--/ Sales By Country -->

            <!-- Total Earning -->
            <div class="col-12 col-md-6 col-xxl-4 order-2 order-xl-0">
                <div class="card h-100">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 card-title">{{ __('admin.Total Earning') }}</h5>
                        </div>
                        <div class="d-flex align-items-center">
                            @php
                                $yearGrowth = $lastYearSales > 0
                                    ? round((($thisYearSales - $lastYearSales) / $lastYearSales) * 100, 1)
                                    : ($thisYearSales > 0 ? 100 : 0);
                            @endphp
                            <h2 class="mb-0 me-2">{{ number_format($thisYearSales, 0) }}</h2>
                            <i class="icon-base ti tabler-chevron-up {{ $yearGrowth >= 0 ? 'text-success' : 'text-danger' }} me-1"></i>
                            <h6 class="{{ $yearGrowth >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                                {{ $yearGrowth >= 0 ? '+' : '' }}{{ number_format($yearGrowth, 1) }}%
                            </h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="totalEarningChart"></div>
                        <div class="d-flex align-items-center my-4">
                            <div class="badge rounded bg-label-primary p-2 me-4 rounded">
                                <i class="icon-base ti tabler-currency-dollar icon-md"></i>
                            </div>
                            <div class="d-flex justify-content-between w-100 gap-2 align-items-center">
                                <div class="me-2">
                                    <h6 class="mb-0">{{ __('admin.this_year') }}</h6>
                                </div>
                                <h6 class="mb-0 text-success">{{ number_format($thisYearSales, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="badge rounded bg-label-secondary p-2 me-4 rounded">
                                <i class="icon-base ti tabler-currency-dollar icon-md"></i>
                            </div>
                            <div class="d-flex justify-content-between w-100 gap-2 align-items-center">
                                <div class="me-2">
                                    <h6 class="mb-0">{{ __('admin.last_year') }}</h6>
                                </div>
                                <h6 class="mb-0 text-success">{{ number_format($lastYearSales, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ Total Earning -->

            <!-- Monthly Campaign State -->
            <div class="col-xxl-4 col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="mb-1">{{ __('admin.most_product_visit') }}</h5>
                            <p class="card-subtitle">{{ __('admin.this_year') }}</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="p-0 m-0">
                            @foreach($mostVisitedProducts as $product)
                            <li class="mb-6 d-flex justify-content-between align-items-center">
                                <div class="badge bg-label-success rounded p-1_5">
                                    <i class="icon-base ti tabler-eye icon-md"></i>
                                </div>
                                <div class="d-flex justify-content-between w-100 flex-wrap">
                                    <h6 class="mb-0 ms-4">{{ is_array($product['name']) ? ($product['name'][app()->getLocale()] ?? $product['name']['en'] ?? 'N/A') : $product['name'] }}</h6>
                                    <div class="d-flex">
                                        <p class="mb-0">{{ $product['orders'] }} {{ __('admin.orders') }}</p>
                                    </div>
                                </div>
                            </li>
                            @endforeach
{{--                            <li class="mb-6 d-flex justify-content-between align-items-center">--}}
{{--                                <div class="badge bg-label-success rounded p-1_5">--}}
{{--                                    <i class="icon-base ti tabler-mail icon-md"></i>--}}
{{--                                </div>--}}
{{--                                <div class="d-flex justify-content-between w-100 flex-wrap">--}}
{{--                                    <h6 class="mb-0 ms-4">Emails</h6>--}}
{{--                                    <div class="d-flex">--}}
{{--                                        <p class="mb-0">12,346</p>--}}
{{--                                        <p class="ms-4 text-success mb-0">0.3%</p>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </li>--}}

{{--                            <li class="mb-3 d-flex justify-content-between align-items-center">--}}
{{--                                <div class="badge bg-label-danger rounded p-1_5">--}}
{{--                                    <i class="icon-base ti tabler-ban icon-md"></i>--}}
{{--                                </div>--}}
{{--                                <div class="d-flex justify-content-between w-100 flex-wrap">--}}
{{--                                    <h6 class="mb-0 ms-4">Unsubscribe</h6>--}}
{{--                                    <div class="d-flex">--}}
{{--                                        <p class="mb-0">86</p>--}}
{{--                                        <p class="ms-4 text-success mb-0">0.8%</p>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </li>--}}
                        </ul>
                    </div>
                </div>
            </div>
            <!--/ Monthly Campaign State -->

            <!-- Source Visit -->
            <div class="col-xxl-4 col-md-6 col-12">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="mb-1">{{ __('admin.most_product_sales') }}</h5>
                            <p class="card-subtitle">{{ __('admin.this_year') }}</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @foreach($mostSoldProducts as $product)
                            <li class="mb-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge bg-label-secondary text-body p-2 me-4 rounded">
                                        <i class="icon-base ti tabler-shopping-cart icon-md"></i>
                                    </div>
                                    <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">{{ is_array($product['name']) ? ($product['name'][app()->getLocale()] ?? $product['name']['en'] ?? 'N/A') : $product['name'] }}</h6>
                                            <small class="text-body">{{ __('admin.sold') }}: {{ $product['sold'] }}</small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <p class="mb-0">{{ $product['sold'] }}</p>
                                            <div class="ms-4 badge bg-label-success">+{{ number_format(($product['sold'] / max(array_sum(array_column($mostSoldProducts->toArray(), 'sold')), 1)) * 100, 1) }}%</div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
{{--                            <li class="mb-6">--}}
{{--                                <div class="d-flex align-items-center">--}}
{{--                                    <div class="badge bg-label-secondary text-body p-2 me-4 rounded">--}}
{{--                                        <i class="icon-base ti tabler-shadow icon-md"></i>--}}
{{--                                    </div>--}}
{{--                                    <div class="d-flex justify-content-between w-100 flex-wrap gap-2">--}}
{{--                                        <div class="me-2">--}}
{{--                                            <h6 class="mb-0">Direct Source</h6>--}}
{{--                                            <small class="text-body">Direct link click</small>--}}
{{--                                        </div>--}}
{{--                                        <div class="d-flex align-items-center">--}}
{{--                                            <p class="mb-0">1.2k</p>--}}
{{--                                            <div class="ms-4 badge bg-label-success">+4.2%</div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </li>--}}
{{--                            <li>--}}
{{--                                <div class="d-flex align-items-center">--}}
{{--                                    <div class="badge bg-label-secondary text-body p-2 me-4 rounded">--}}
{{--                                        <i class="icon-base ti tabler-star icon-md"></i>--}}
{{--                                    </div>--}}
{{--                                    <div class="d-flex justify-content-between w-100 flex-wrap gap-2">--}}
{{--                                        <div class="me-2">--}}
{{--                                            <h6 class="mb-0">Other</h6>--}}
{{--                                            <small class="text-body">Many Sources</small>--}}
{{--                                        </div>--}}
{{--                                        <div class="d-flex align-items-center">--}}
{{--                                            <p class="mb-0">12.5k</p>--}}
{{--                                            <div class="ms-4 badge bg-label-success">+6.2%</div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </li>--}}
                        </ul>
                    </div>
                </div>
            </div>
            <!--/ Source Visit -->

            <!-- Projects table -->
            <div class="col-xxl-8">
                <div class="card">
{{--                    <div class="table-responsive mb-4">--}}
{{--                        <table class="table datatable-project table-sm">--}}
{{--                            <thead class="border-top">--}}
{{--                            <tr>--}}
{{--                                <th></th>--}}
{{--                                <th></th>--}}
{{--                                <th>Project</th>--}}
{{--                                <th>Leader</th>--}}
{{--                                <th>Team</th>--}}
{{--                                <th class="w-px-200">Progress</th>--}}
{{--                                <th>Action</th>--}}
{{--                            </tr>--}}
{{--                            </thead>--}}
{{--                        </table>--}}
{{--                    </div>--}}
                </div>
            </div>
            <!--/ Projects table -->
        </div>
    </div>
@endsection
@section('dashboard-footer')
    <script>
        // Pass data to JavaScript
        window.dashboardData = {
            averageDailySales: @json($last7DaysSales),
            last7Days: @json($last7Days),
            supportTracker: {
                series: [{{ $supportTrackerPercentage }}],
                labels: ['{{ __('admin.completed_orders') }}'],
                data: @json($supportTrackerData),
                labels2: @json($supportTrackerLabels)
            },
            weeklyEarnings: @json($weeklyEarnings),
            weeklyLabels: @json($weeklyLabels),
            totalEarning: {
                earnings: @json($monthlyEarnings),
                expenses: @json($monthlyExpenses)
            }
        };
    </script>
    <script src="{{ asset('dashboard') }}/assets/js/dashboards-analytics.js"></script>
    <script src="{{ asset('dashboard') }}/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
    <script>
        function refreshSmsBalance() {
            const btn = event.target.closest('button');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="icon-base ti tabler-loader-2 spin"></i>';

            fetch('{{ route("admin.sms.balance.refresh") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const balance = data.balance;
                    const balanceDisplay = document.getElementById('sms-balance-display');
                    if (balanceDisplay) {
                        balanceDisplay.textContent = isNaN(balance) ? balance : parseInt(balance).toLocaleString();
                    }

                } else {

                }
            })
            .catch(error => {
                console.error('Error:', error);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        }
    </script>
    <style>
        .spin {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
@endsection
