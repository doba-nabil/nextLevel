<div class="proCont_wrapper">
    <!-- Premium Wallet Card -->
    <div class="wallet-premium-card">
        <div class="wallet-chip mb-3">
            <i class="fa-solid fa-wallet fa-2xl opacity-50"></i>
        </div>
        <div class="sub_title text-dark opacity-75 fw-bold mb-1"> {{ __('website.available_balance') }} </div>
        <div class="balance_num display-5 fw-800 mb-4">
            {{ number_format($walletBalance ?? 0, 0) }}
            <span class="fs-4">{{ __('website.currency') ?? 'KD' }}</span>
        </div>

        <div class="d-flex justify-content-center gap-3">
            <button data-tab="add_money" class="btn btn-dark tab_link premium-modal-btn px-4 py-2 d-flex align-items-center gap-2">
                <i class="fa fa-plus"></i> {{ __('website.add_money') }}
            </button>
        </div>

        <!-- Abstract decorations -->
        <div class="wallet-circle-1"></div>
        <div class="wallet-circle-2"></div>
    </div>

    <!-- Transaction History Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
        <h3 class="profile-section-title mb-0">
            <i class="fa-solid fa-clock-rotate-left gold-icon"></i>
            {{ __('website.transaction_history') ?? 'Transaction History' }}
        </h3>
    </div>

    <div class="transactions-list">
        @if($transactions && $transactions->count() > 0)
            @foreach($transactions as $transaction)
                <div class="profile-content-card mb-3 p-3 transition-hover">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="transaction-icon {{ $transaction->amount > 0 ? 'bg-success-light' : 'bg-danger-light' }}">
                                <i class="fa-solid {{ $transaction->amount > 0 ? 'fa-arrow-down' : 'fa-arrow-up' }} {{ $transaction->amount > 0 ? 'text-success' : 'text-danger' }}"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ $transaction->description ?? 'Transaction' }}</div>
                                <div class="text-muted small">
                                    <i class="fa-regular fa-calendar me-1"></i>
                                    {{ $transaction->created_at->format('d M Y, h:i A') }}
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-800 fs-5 {{ $transaction->amount > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount, 0) }}
                                <span class="small">{{ __('website.currency') ?? 'KD' }}</span>
                            </div>
                            <span class="badge {{ $transaction->amount > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-pill px-3 py-2 mt-1">
                                {{ $transaction->amount > 0 ? (__('website.added') ?? 'Added') : (__('website.used') ?? 'Used') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="profile-content-card text-center py-5">
                <div class="mb-3">
                    <i class="fa-solid fa-receipt fa-3x text-muted opacity-25"></i>
                </div>
                <h5 class="text-muted">{{ __('website.no_transactions_found') }}</h5>
                <p class="text-muted small">{{ __('website.your_recent_activity_will_appear_here') ?? 'Your recent wallet activity will appear here' }}</p>
            </div>
        @endif
    </div>
</div>

