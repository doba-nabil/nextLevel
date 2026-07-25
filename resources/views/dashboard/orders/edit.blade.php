@extends('dashboard.layout.master')
@section('title', __('admin.edit') . ' ' . __('admin.order_number') . ' #' . $order->order_number)

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>{{ __('admin.edit') }} {{ __('admin.order_number') }}: #{{ $order->order_number }}</h5>
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                            <i class="icon-base ti tabler-arrow-left"></i> {{ __('admin.back') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('orders.update', $order->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.status') }}</label>
                                    <select class="form-control" name="status" required>
                                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>{{ __('admin.pending') }}</option>
                                        <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>{{ __('admin.processing') }}</option>
                                        <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>{{ __('admin.completed') }}</option>
                                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>{{ __('admin.cancelled') }}</option>
                                    </select>
                                    @error('status')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.payment_status') }}</label>
                                    <select class="form-control" name="payment_status">
                                        <option value="pending" {{ ($order->payment_status ?? 'pending') === 'pending' ? 'selected' : '' }}>{{ __('admin.pending') }}</option>
                                        <option value="paid" {{ ($order->payment_status ?? '') === 'paid' ? 'selected' : '' }}>{{ __('admin.paid') }}</option>
                                        <option value="failed" {{ ($order->payment_status ?? '') === 'failed' ? 'selected' : '' }}>{{ __('admin.failed') }}</option>
                                    </select>
                                </div>
                            </div>

                            <hr>

                            <h6 class="text-primary mb-3">{{ __('admin.customer') }} {{ __('admin.information') }}</h6>
                            <div class="row mb-3">
                                @if($order->user_id && $order->user)
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('admin.name') }}</label>
                                        <input type="text" class="form-control" value="{{ $order->user->name }}" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('admin.email') }}</label>
                                        <input type="email" class="form-control" value="{{ $order->user->email }}" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('admin.phone') }}</label>
                                        <input type="text" class="form-control" value="{{ $order->user->phone ?? '-' }}" disabled>
                                    </div>
                                @else
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('admin.name') }}</label>
                                        <input type="text" class="form-control" name="guest_name" value="{{ $order->guest_name ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('admin.email') }}</label>
                                        <input type="email" class="form-control" name="guest_email" value="{{ $order->guest_email ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('admin.phone') }}</label>
                                        <input type="text" class="form-control" name="guest_phone" value="{{ $order->guest_phone ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('admin.address') }}</label>
                                        <textarea class="form-control" name="guest_address">{{ $order->guest_address ?? '' }}</textarea>
                                    </div>
                                @endif
                            </div>

                            <hr>

                            <h6 class="text-primary mb-3">{{ __('admin.order_items') }}</h6>
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>{{ __('admin.product') }}</th>
                                            <th>{{ __('admin.quantity') }}</th>
                                            <th>{{ __('admin.price') }}</th>
                                            <th>{{ __('admin.total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items->whereNull('parent_item_id') as $item)
                                            <tr>
                                                <td>
                                                    {{ $item->product->name ?? 'N/A' }}
                                                    @if($item->addons && $item->addons->count() > 0)
                                                        <br><small class="text-muted">+ {{ $item->addons->pluck('addon.name')->implode(', ') }}</small>
                                                    @endif
                                                    @if($item->children && $item->children->count() > 0)
                                                        <br><small class="text-muted">→ {{ $item->children->pluck('product.name')->implode(', ') }}</small>
                                                    @elseif($item->meta && isset($item->meta['is_box']) && $item->meta['is_box'] && isset($item->meta['subproducts']))
                                                        @php
                                                            $spNames = [];
                                                            foreach($item->meta['subproducts'] as $sp) {
                                                                if(isset($sp['product_id']) && isset($subProducts[$sp['product_id']])) {
                                                                    $spName = is_array($subProducts[$sp['product_id']]) 
                                                                        ? ($subProducts[$sp['product_id']][app()->getLocale()] ?? $subProducts[$sp['product_id']]['en'] ?? 'Name')
                                                                        : $subProducts[$sp['product_id']];
                                                                    $spNames[] = $spName;
                                                                }
                                                            }
                                                        @endphp
                                                        @if(count($spNames) > 0)
                                                            <br><small class="text-muted">→ {{ implode(', ', $spNames) }}</small>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>{{ number_format($item->price, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</td>
                                                <td>{{ number_format($item->total, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="row">
                                <div class="col-md-6 offset-md-6">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tr>
                                                <td><strong>{{ __('admin.total') }}:</strong></td>
                                                <td class="text-end"><strong>{{ number_format($order->total, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                                <a href="{{ route('orders.index') }}" class="btn btn-secondary">{{ __('admin.cancel') }}</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('dashboard-head')
    @include('dashboard.partials.create.css')
@endsection

@section('dashboard-footer')
    @include('dashboard.partials.create.js')
@endsection

















