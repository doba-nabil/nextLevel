<div class="proCont_wrapper">
    <h3 class="profile-section-title">
        <i class="fa-solid fa-map-location gold-icon"></i>
        {{ __('website.track_order') }}
    </h3>

    @if($trackingOrders && $trackingOrders->count() > 0)
        @foreach($trackingOrders as $order)
            <div class="profile-content-card mb-4">
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <div class="info-item-card border-0 p-0 mb-3">
                            <div>
                                <div class="info-label">{{ __('website.order_number') }}</div>
                                <div class="info-value">#{{ $order->order_number }}</div>
                            </div>
                        </div>
                        <div class="info-item-card border-0 p-0 mb-3">
                            <div>
                                <div class="info-label">{{ __('website.order_date') }}</div>
                                <div class="info-value">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                            </div>
                        </div>
                        <div class="info-item-card border-0 p-0">
                            <div>
                                <div class="info-label">{{ __('website.total_amount') }}</div>
                                <div class="info-value text-success">{{ number_format($order->total, 3) }} {{ session('currency', 'KD') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 text-md-end">
                        <div class="info-label">{{ __('website.current_status') }}</div>
                        <span class="status-badge status-{{ $order->status }} mt-1">
                            {{ ucfirst(__('website.' . $order->status)) }}
                        </span>
                    </div>
                </div>

                <!-- Order Progress Tracking -->
                <div class="mt-5 pt-4 border-top">
                    <h6 class="fw-bold mb-4">{{ __('website.order_progress') }}:</h6>
                    <div class="progress-steps-premium">
                        <div class="step-item {{ $order->status === 'pending' ? 'active' : ($order->status === 'processing' || $order->status === 'delivered' ? 'completed' : '') }}">
                            <div class="step-icon">
                                <i class="fa-solid fa-file-invoice"></i>
                            </div>
                            <span class="step-label">{{ __('website.order_placed') }}</span>
                        </div>
                        <div class="step-item {{ $order->status === 'processing' ? 'active' : ($order->status === 'delivered' ? 'completed' : '') }}">
                            <div class="step-icon">
                                <i class="fa-solid fa-gear"></i>
                            </div>
                            <span class="step-label">{{ __('website.processing') }}</span>
                        </div>
                        <div class="step-item {{ $order->status === 'delivered' ? 'active completed' : '' }}">
                            <div class="step-icon">
                                <i class="fa-solid fa-truck-ramp-box"></i>
                            </div>
                            <span class="step-label">{{ __('website.delivered') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="profile-content-card text-center py-5">
            <div class="empty-icon mb-4">
                <i class="fa-solid fa-magnifying-glass-location" style="font-size: 60px; color: #f1f1f1;"></i>
            </div>
            <h4>{{ __('website.no_orders_to_track') }}</h4>
            <p class="text-muted">{{ __('website.no_active_orders_message') ?? 'You have no active orders to track at the moment.' }}</p>
            <a href="{{ route('website.home') }}" class="main_bttn mid_bttn mt-3">{{ __('website.start_shopping') }}</a>
        </div>
    @endif
</div>


