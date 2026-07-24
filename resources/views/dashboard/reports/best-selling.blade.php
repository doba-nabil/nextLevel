@extends('dashboard.layout.master')
@section('title', __('admin.best_selling_products'))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header d-flex justify-content-between">
                        {{ __('admin.best_selling_products') }}
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="exportDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="icon-base ti tabler-download me-2"></i> {{ __('admin.export') }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('reports.export.excel', 'best-selling') . '?' . http_build_query(request()->all()) }}">
                                        <i class="icon-base ti tabler-file-spreadsheet text-success"></i> Excel
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('reports.export.csv', 'best-selling') . '?' . http_build_query(request()->all()) }}">
                                        <i class="icon-base ti tabler-file text-info"></i> CSV
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('reports.export.pdf', 'best-selling') . '?' . http_build_query(request()->all()) }}" target="_blank">
                                        <i class="icon-base ti tabler-file-text text-danger"></i> PDF
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </h5>
                    <div class="card-body">
                        <!-- Filters -->
                        <form method="GET" action="{{ route('reports.best-selling') }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('admin.date_range') }}</label>
                                    <select name="date_range" class="form-select">
                                        <option value="today" {{ $filters['date_range'] == 'today' ? 'selected' : '' }}>{{ __('admin.today') }}</option>
                                        <option value="week" {{ $filters['date_range'] == 'week' ? 'selected' : '' }}>{{ __('admin.this_week') }}</option>
                                        <option value="month" {{ $filters['date_range'] == 'month' ? 'selected' : '' }}>{{ __('admin.this_month') }}</option>
                                        <option value="year" {{ $filters['date_range'] == 'year' ? 'selected' : '' }}>{{ __('admin.this_year') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('admin.category') }}</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">{{ __('admin.all') }}</option>
                                        @foreach($filterOptions['categories'] as $category)
                                            <option value="{{ $category->id }}" {{ $filters['category_id'] == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
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
                                    <label class="form-label">{{ __('admin.limit') }}</label>
                                    <input type="number" name="limit" class="form-control" value="{{ $filters['limit'] }}" min="1" max="100">
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">{{ __('admin.filter') }}</button>
                                </div>
                            </div>
                        </form>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('admin.product') }}</th>
                                        <th>{{ __('admin.category') }}</th>
                                        <th>{{ __('admin.total_quantity') }}</th>
                                        <th>{{ __('admin.total_revenue') }}</th>
                                        <th>{{ __('admin.order_count') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bestSelling as $index => $product)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                @php
                                                    $productName = is_array($product->name) ? ($product->name[app()->getLocale()] ?? $product->name['ar'] ?? $product->name['en'] ?? '') : $product->name;
                                                @endphp
                                                {{ $productName }}
                                            </td>
                                            <td>
                                                @php
                                                    $categoryName = is_array($product->category_name) ? ($product->category_name[app()->getLocale()] ?? $product->category_name['ar'] ?? $product->category_name['en'] ?? '-') : ($product->category_name ?? '-');
                                                @endphp
                                                {{ $categoryName }}
                                            </td>
                                            <td>{{ $product->total_quantity }}</td>
                                            <td>{{ number_format($product->total_revenue, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</td>
                                            <td>{{ $product->order_count }}</td>
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
