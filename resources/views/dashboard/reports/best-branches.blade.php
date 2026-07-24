@extends('dashboard.layout.master')
@section('title', __('admin.best_branches'))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header d-flex justify-content-between">
                        {{ __('admin.best_branches') }}
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="exportDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="icon-base ti tabler-download me-2"></i> {{ __('admin.export') }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('reports.export.excel', 'best-branches') . '?' . http_build_query(request()->all()) }}">
                                        <i class="icon-base ti tabler-file-spreadsheet text-success"></i> Excel
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('reports.export.csv', 'best-branches') . '?' . http_build_query(request()->all()) }}">
                                        <i class="icon-base ti tabler-file text-info"></i> CSV
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('reports.export.pdf', 'best-branches') . '?' . http_build_query(request()->all()) }}" target="_blank">
                                        <i class="icon-base ti tabler-file-text text-danger"></i> PDF
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </h5>
                    <div class="card-body">
                        <!-- Filters -->
                        <form method="GET" action="{{ route('reports.best-branches') }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('admin.date_range') }}</label>
                                    <select name="date_range" class="form-select">
                                        <option value="today" {{ $filters['date_range'] == 'today' ? 'selected' : '' }}>{{ __('admin.today') }}</option>
                                        <option value="week" {{ $filters['date_range'] == 'week' ? 'selected' : '' }}>{{ __('admin.this_week') }}</option>
                                        <option value="month" {{ $filters['date_range'] == 'month' ? 'selected' : '' }}>{{ __('admin.this_month') }}</option>
                                        <option value="year" {{ $filters['date_range'] == 'year' ? 'selected' : '' }}>{{ __('admin.this_year') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('admin.status') }}</label>
                                    <select name="status" class="form-select">
                                        <option value="">{{ __('admin.all') }}</option>
                                        @foreach($filterOptions['statuses'] as $status)
                                            <option value="{{ $status }}" {{ $filters['status'] == $status ? 'selected' : '' }}>{{ __('admin.' . $status) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary mt-4">{{ __('admin.filter') }}</button>
                                </div>
                            </div>
                        </form>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('admin.branch') }}</th>
                                        <th>{{ __('admin.total_orders') }}</th>
                                        <th>{{ __('admin.total_revenue') }}</th>
                                        <th>{{ __('admin.avg_order_value') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bestBranches as $index => $branch)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                @php
                                                    $branchName = is_array($branch->name) ? ($branch->name[app()->getLocale()] ?? $branch->name['ar'] ?? $branch->name['en'] ?? '') : $branch->name;
                                                @endphp
                                                {{ $branchName }}
                                            </td>
                                            <td>{{ $branch->total_orders }}</td>
                                            <td>{{ number_format($branch->total_revenue, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</td>
                                            <td>{{ number_format($branch->avg_order_value, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
