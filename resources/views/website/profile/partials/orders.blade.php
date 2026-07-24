<div class="proCont_wrapper">
    <h3 class="profile-section-title mb-4">
        <i class="fa fa-shopping-bag gold-icon"></i>
        {{ __('website.my_orders') }}
    </h3>

    @if($orders && $orders->count() > 0)
        @foreach($orders as $order)
            <div class="order-premium-card mb-4" id="order-card-{{ $order->id }}">
                <!-- Order Header -->
                <div class="order-top-bar">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-4 flex-wrap">
                                <div>
                                    <div class="small-label">{{ __('website.order_number') }}</div>
                                    <div class="order-id">#{{ $order->order_number }}</div>
                                </div>
                                <div class="sep-line"></div>
                                <div>
                                    <div class="small-label">{{ __('website.order_date') }}</div>
                                    <div class="order-meta">
                                        <i class="fa fa-calendar-day me-1"></i>
                                        {{ $order->created_at->format('d M Y') }}
                                    </div>
                                </div>
                                <div class="sep-line d-none d-lg-block"></div>
                                <div>
                                    <div class="small-label">{{ __('website.order_type') }}</div>
                                    <div class="order-meta">
                                        <i class="fa fa-{{ $order->type === 'delivery' ? 'truck' : 'store' }} me-1"></i>
                                        {{ ucfirst($order->type ?? 'Delivery') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <span class="status-badge status-{{ $order->status }}">
                                {{ ucfirst(__('website.' . $order->status)) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Order Items Mini-Grid -->
                <div class="order-items-preview">
                    <div class="row g-3">
                        @foreach($order->items->take(3) as $item)
                            <div class="col-md-4 col-12">
                                <div class="item-mini-card">
                                    @php
                                        $settingModel = \App\Models\Setting::getSettingModel();
                                        $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
                                        $productImage = $item->product && $item->product->getFirstMediaUrl('products') ? $item->product->getFirstMediaUrl('products') : null;
                                    @endphp
                                    <img src="{{ $productImage ?: $logoUrl }}" alt="{{ $item->product->name ?? 'Product' }}" class="item-thumb">
                                    <div class="item-details">
                                        <div class="item-name">{{ $item->product->name ?? 'N/A' }}</div>
                                        <div class="item-qty">{{ __('website.qty') }}: {{ $item->quantity }}</div>
                                        <div class="item-price">{{ number_format((float)$item->price, 3) }} {{ session('currency', 'KD') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if($order->items->count() > 3)
                            <div class="col-md-4 col-12">
                                <div class="item-mini-card more-items">
                                    +{{ $order->items->count() - 3 }} {{ __('website.more_items') ?? 'More' }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- NEW: Integrated Tracking Section (Hidden by default) -->
                <div class="order-tracking-collapse" id="tracking-{{ $order->id }}" style="display: none;">
                    <div class="tracking-content p-4 border-top bg-light-soft">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-800 mb-0">
                                <i class="fa-solid fa-route gold-icon me-2"></i>
                                {{ __('website.order_progress') }}
                            </h6>
                            <span class="text-muted small">{{ __('website.current_status') }}: <strong>{{ ucfirst(__('website.' . $order->status)) }}</strong></span>
                        </div>

                        <div class="progress-steps-premium horizontal-steps">
                            <div class="step-item {{ $order->status === 'pending' ? 'active' : ($order->status === 'processing' || $order->status === 'delivered' || $order->status === 'shipped' ? 'completed' : '') }}">
                                <div class="step-icon">
                                    <i class="fa-solid fa-file-invoice"></i>
                                </div>
                                <span class="step-label">{{ __('website.order_placed') }}</span>
                            </div>
                            <div class="step-item {{ $order->status === 'processing' ? 'active' : ($order->status === 'shipped' || $order->status === 'delivered' ? 'completed' : '') }}">
                                <div class="step-icon">
                                    <i class="fa-solid fa-spinner fa-spin-slow"></i>
                                </div>
                                <span class="step-label">{{ __('website.processing') }}</span>
                            </div>
                            <div class="step-item {{ $order->status === 'shipped' ? 'active' : ($order->status === 'delivered' ? 'completed' : '') }}">
                                <div class="step-icon">
                                    <i class="fa-solid fa-truck"></i>
                                </div>
                                <span class="step-label">{{ __('website.shipped') ?? 'Shipped' }}</span>
                            </div>
                            <div class="step-item {{ $order->status === 'delivered' ? 'active completed' : '' }}">
                                <div class="step-icon">
                                    <i class="fa-solid fa-check-double"></i>
                                </div>
                                <span class="step-label">{{ __('website.delivered') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Footer -->
                <div class="order-bottom-bar">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <div class="payment-info">
                                <i class="fa-solid fa-credit-card me-2"></i>
                                <span>{{ __('website.payment') }}:</span>
                                <strong>{{ ucfirst($order->payment_method ?? 'Cash') }}</strong>
                            </div>
                        </div>
                        <div class="col-md-7 text-md-end mt-2 mt-md-0 d-flex align-items-center justify-content-md-end gap-3 flex-wrap">
                            <div class="order-total me-md-2">
                                <span class="label">{{ __('website.total_amount') }}:</span>
                                <span class="amount">{{ number_format((float)$order->total, 3) }} {{ session('currency', 'KD') }}</span>
                            </div>
                            <button type="button" class="btn btn-dark premium-modal-btn btn-sm btn-track-order" 
                                    data-order-id="{{ $order->id }}"
                                    data-order-number="#{{ $order->order_number }}"
                                    data-order-date="{{ $order->created_at->format('d M Y') }}"
                                    data-order-status="{{ ucfirst(__('website.' . $order->status)) }}"
                                    data-order-total="{{ number_format((float)$order->total, 3) }} {{ session('currency', 'KD') }}"
                                    data-payment-method="{{ ucfirst($order->payment_method ?? 'Cash') }}"
                                    data-order-status-value="{{ $order->status }}">
                                <i class="fa-solid fa-location-dot me-1"></i> {{ __('website.track_order') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Pagination -->
        @if($orders->hasPages())
            <div class="d-flex justify-content-center mt-5">
                <div class="custom-pagination-wrapper">
                    {{ $orders->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @endif
    @else
        <div class="profile-content-card text-center py-5">
            <div class="empty-icon mb-4">
                <i class="fa-solid fa-box-open" style="font-size: 60px; color: #f1f1f1;"></i>
            </div>
            <h4 class="fw-bold">{{ __('website.no_orders_yet') }}</h4>
            <p class="text-muted">{{ __('website.no_orders_message') }}</p>
            <a href="{{ route('website.home') }}" class="main_bttn mid_bttn mt-3">
                {{ __('website.start_shopping') }}
            </a>
        </div>
    @endif
</div>

<!-- Order Tracking Modal -->
<div class="modal fade" id="orderTrackingModal" tabindex="-1" aria-labelledby="orderTrackingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title d-flex align-items-center gap-2" id="orderTrackingModalLabel">
                    <i class="fa-solid fa-route text-warning"></i>
                    {{ __('website.track_order') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pt-3" id="orderTrackingContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">{{ __('website.loading') ?? 'Loading...' }}</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4">
                <button type="button" class="btn btn-light premium-modal-btn" data-bs-dismiss="modal">{{ __('website.close') }}</button>
            </div>
        </div>
    </div>
</div>
