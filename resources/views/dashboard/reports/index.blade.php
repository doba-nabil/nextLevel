@extends('dashboard.layout.master')
@section('title', __('admin.reports'))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header d-flex justify-content-between">
                        {{ __('admin.reports_overview') }}
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="exportDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="icon-base ti tabler-download me-2"></i> {{ __('admin.export') }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('reports.export.excel', 'overview') . '?' . http_build_query(request()->all()) }}">
                                        <i class="icon-base ti tabler-file-spreadsheet text-success"></i> Excel
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('reports.export.csv', 'overview') . '?' . http_build_query(request()->all()) }}">
                                        <i class="icon-base ti tabler-file text-info"></i> CSV
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('reports.export.pdf', 'overview') . '?' . http_build_query(request()->all()) }}" target="_blank">
                                        <i class="icon-base ti tabler-file-text text-danger"></i> PDF
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </h5>
                    <div class="card-body">
                        <!-- Filters -->
                        <form method="GET" action="{{ route('reports.index') }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('admin.date_range') }}</label>
                                    <select name="date_range" class="form-select" id="date-range-select">
                                        <option value="today" {{ $filters['date_range'] == 'today' ? 'selected' : '' }}>{{ __('admin.today') }}</option>
                                        <option value="week" {{ $filters['date_range'] == 'week' ? 'selected' : '' }}>{{ __('admin.this_week') }}</option>
                                        <option value="month" {{ $filters['date_range'] == 'month' ? 'selected' : '' }}>{{ __('admin.this_month') }}</option>
                                        <option value="year" {{ $filters['date_range'] == 'year' ? 'selected' : '' }}>{{ __('admin.this_year') }}</option>
                                        <option value="custom" {{ $filters['date_range'] == 'custom' ? 'selected' : '' }}>{{ __('admin.custom') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3" id="start-date-group" style="display: {{ $filters['date_range'] == 'custom' ? 'block' : 'none' }};">
                                    <label class="form-label">{{ __('admin.start_date') }}</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] }}">
                                </div>
                                <div class="col-md-3" id="end-date-group" style="display: {{ $filters['date_range'] == 'custom' ? 'block' : 'none' }};">
                                    <label class="form-label">{{ __('admin.end_date') }}</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('admin.status') }}</label>
                                    <select name="status" class="form-select">
                                        <option value="">{{ __('admin.all') }}</option>
                                        @foreach($filterOptions['statuses'] as $status)
                                            <option value="{{ $status }}" {{ $filters['status'] == $status ? 'selected' : '' }}>{{ __('admin.' . $status) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('admin.payment_method') }}</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="">{{ __('admin.all') }}</option>
                                        @foreach($filterOptions['payment_methods'] as $method)
                                            <option value="{{ $method }}" {{ $filters['payment_method'] == $method ? 'selected' : '' }}>{{ $method }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('admin.branch') }}</label>
                                    <select name="branch_id" class="form-select">
                                        <option value="">{{ __('admin.all') }}</option>
                                        @foreach($filterOptions['branches'] as $branch)
                                            <option value="{{ $branch->id }}" {{ $filters['branch_id'] == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('admin.user') }}</label>
                                    <select name="user_id" class="form-select">
                                        <option value="">{{ __('admin.all') }}</option>
                                        @foreach($filterOptions['users'] as $user)
                                            <option value="{{ $user->id }}" {{ $filters['user_id'] == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">{{ __('admin.filter') }}</button>
                                    <a href="{{ route('reports.index') }}" class="btn btn-secondary">{{ __('admin.reset') }}</a>
                                </div>
                            </div>
                        </form>

                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-label-primary">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ __('admin.total_orders') }}</h6>
                                        <h3 class="mb-0">{{ $stats['total_orders'] }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-label-success">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ __('admin.total_revenue') }}</h6>
                                        <h3 class="mb-0">{{ number_format($stats['total_revenue'], 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-label-info">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ __('admin.avg_order_value') }}</h6>
                                        <h3 class="mb-0">{{ number_format($stats['avg_order_value'], 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-label-warning">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ __('admin.completed_orders') }}</h6>
                                        <h3 class="mb-0">{{ $stats['completed_orders'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Links -->
                        <div class="row">
                            <div class="col-md-4">
                                <a href="{{ route('reports.best-selling') }}" class="card text-decoration-none">
                                    <div class="card-body text-center">
                                        <i class="icon-base ti tabler-trophy icon-48px mb-2"></i>
                                        <h5>{{ __('admin.best_selling_products') }}</h5>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('reports.best-branches') }}" class="card text-decoration-none">
                                    <div class="card-body text-center">
                                        <i class="icon-base ti tabler-building-store icon-48px mb-2"></i>
                                        <h5>{{ __('admin.best_branches') }}</h5>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('reports.payment-methods') }}" class="card text-decoration-none">
                                    <div class="card-body text-center">
                                        <i class="icon-base ti tabler-credit-card icon-48px mb-2"></i>
                                        <h5>{{ __('admin.payment_methods_report') }}</h5>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('dashboard-footer')
    <script>
        document.getElementById('date-range-select').addEventListener('change', function() {
            const isCustom = this.value === 'custom';
            document.getElementById('start-date-group').style.display = isCustom ? 'block' : 'none';
            document.getElementById('end-date-group').style.display = isCustom ? 'block' : 'none';
        });
    </script>
@endsection
