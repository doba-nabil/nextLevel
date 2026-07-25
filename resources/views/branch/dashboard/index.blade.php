@extends('branch.layouts.master')

@section('title')
    {{ __('admin.dashboard') }}
@endsection

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span>{{ __('admin.total_orders') }}</span>
                                <div class="d-flex align-items-center my-2">
                                    <h3 class="mb-0 me-2">{{ $totalOrders }}</h3>
                                </div>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                  <i class="icon-base ti tabler-shopping-cart icon-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span>{{ __('admin.pending_orders') }}</span>
                                <div class="d-flex align-items-center my-2">
                                    <h3 class="mb-0 me-2">{{ $pendingOrders }}</h3>
                                </div>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                  <i class="icon-base ti tabler-clock icon-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span>{{ __('admin.completed_orders') }}</span>
                                <div class="d-flex align-items-center my-2">
                                    <h3 class="mb-0 me-2">{{ $completedOrders }}</h3>
                                </div>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                  <i class="icon-base ti tabler-check icon-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Orders Table -->
        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">{{ __('admin.latest_orders') ?? 'أحدث الطلبات' }}</h5>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('admin.order_number') ?? 'رقم الطلب' }}</th>
                        <th>{{ __('admin.customer') ?? 'العميل' }}</th>
                        <th>{{ __('admin.total') ?? 'الإجمالي' }}</th>
                        <th>{{ __('admin.status') ?? 'الحالة' }}</th>
                        <th>{{ __('admin.date') ?? 'التاريخ' }}</th>
                    </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                    @forelse($latestOrders as $order)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $order->order_number ?? $order->id }}</td>
                            <td>{{ $order->user?->name ?? 'Guest' }}</td>
                            <td>{{ $order->total }}</td>
                            <td>
                                @if($order->status == 'completed')
                                    <span class="badge bg-label-success">{{ __('admin.completed') }}</span>
                                @elseif($order->status == 'pending')
                                    <span class="badge bg-label-warning">{{ __('admin.pending') }}</span>
                                @else
                                    <span class="badge bg-label-secondary">{{ $order->status }}</span>
                                @endif
                            </td>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">{{ __('admin.no_data_found') ?? 'لا يوجد طلبات' }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
