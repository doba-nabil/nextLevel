@extends('website.layout.master')
@section('title', __('website.payment'))
@section('body', 'bg-white grey_mob')
@section('website-main')
    <!-- CSS Link -->
    <link rel="stylesheet" href="{{ asset('website/assets/css/checkout_redesign.css') }}">

    <!-- BreadCrumb -->

    <div class="breadCrumb_section midPadding">
        <div class="container px-lg-0">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('website.home') }}">{{ __('website.home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cart.index') }}">{{ __('website.cart') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ __('website.payment') }}
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Payment Section -->
    <section class="pickup_section secPadding pt_sm_0 pt-5 mb-5">
        <div class="container px-lg-0">


            <div class="row">
                <div class="col-12 col-lg-8 mx-lg-auto">
                    <form method="POST" action="{{ route('website.orders.payment.process', $order->id) }}" id="payment-form">
                        @csrf
                        <input type="hidden" name="pay_type" id="pay_type_input" value="knet">

                        <div class="mealCol_wrap">
                            <h3 class="asideSM_title">
                                {{ __('website.choose_payment_method') }}
                            </h3>

                            @php
                                $userWalletBalance = 0;
                                if(auth('web')->check() && auth('web')->user()->wallet) {
                                    $userWalletBalance = (float) auth('web')->user()->wallet->balance;
                                }
                                $orderTotal = (float) $order->total;
                                $canPayWithWallet = $userWalletBalance > 0;
                                $canPayFullWithWallet = $userWalletBalance >= $orderTotal;
                                $hasWallet = auth('web')->check() && auth('web')->user()->wallet;
                            @endphp

                            <!-- Payment Methods -->
                            <div class="paymentLG_flex mb-4">
                                <!-- KNET -->
                                <div class="visaOne_Crelative">
<input type="radio"
       class="absRadio_check payment-method-radio"
       name="payment_method"
                                           value="knet"
       id="knet_payment"
       data-payment-type="knet"
                                           data-pay-type="knet"
       {{ !$canPayFullWithWallet ? 'checked' : '' }}>
                                    <label for="knet_payment" class="visaOne_cardN">
                                        <img src="{{ asset('website/assets/img/knet.png') }}" alt="" class="visaOne_cIMG">
                                        <div class="visaOne_name"> KNET </div>
                                    </label>
                                </div>

                                <!-- Credit Card -->
                                <div class="visaOne_Crelative">
                                    <input type="radio"
                                           class="absRadio_check payment-method-radio"
                                           name="payment_method"
                                           value="credit"
                                           id="credit_payment"
                                           data-payment-type="credit"
                                           data-pay-type="credit">
                                    <label for="credit_payment" class="visaOne_cardN">
                                        <img src="{{ asset('website/assets/img/visa.png') }}" alt="Credit Card" class="visaOne_cIMG" onerror="this.src='{{ asset('website/assets/img/knet.png') }}'">
                                        <div class="visaOne_name"> {{ __('website.credit_card') }} </div>
                                    </label>
                                </div>


                                <!-- Apple Pay -->
                                <div class="visaOne_Crelative">
                                    <input type="radio"
                                           class="absRadio_check payment-method-radio"
                                           name="payment_method"
                                           value="applepay"
                                           id="applepay_payment"
                                           data-payment-type="applepay"
                                           data-pay-type="applepay">
                                    <label for="applepay_payment" class="visaOne_cardN">
                                        <img src="{{ asset('website/assets/img/apay.png') }}" alt="Apple Pay" class="visaOne_cIMG" onerror="this.src='{{ asset('website/assets/img/knet.png') }}'">
                                        <div class="visaOne_name"> Apple Pay </div>
                                    </label>
                                </div>

                                @if($hasWallet)
                                    <!-- Wallet Payment -->
                                    <div class="visaOne_Crelative {{ !$canPayFullWithWallet ? 'opacity-50' : '' }}" style="{{ !$canPayFullWithWallet ? 'pointer-events: none; cursor: not-allowed;' : '' }}">
                                        <input type="radio"
                                               class="absRadio_check payment-method-radio"
                                               name="payment_method"
                                               value="wallet"
                                               id="wallet_payment"
                                               {{ $canPayFullWithWallet ? 'checked' : '' }}
                                               {{ !$canPayFullWithWallet ? 'disabled' : '' }}>
                                        <label for="wallet_payment" class="visaOne_cardN" style="{{ !$canPayFullWithWallet ? 'cursor: not-allowed;' : '' }}">
                                            @if($canPayFullWithWallet)
                                                <img src="{{ asset('website/assets/img/wallet-epayment.png') }}" alt="Wallet" class="visaOne_cIMG" onerror="this.src='{{ asset('website/assets/img/w.png') }}'">
                                            @else
                                                <img src="{{ asset('website/assets/img/w.png') }}" alt="Wallet" class="visaOne_cIMG">
                                            @endif
                                            <div class="visaOne_name">
                                                {{ __('website.wallet') }}
                                                <small class="d-block {{ $canPayFullWithWallet ? 'text-success' : 'text-danger' }}">
                                                    {{ __('website.balance') ?? 'Balance' }}: {{ number_format($userWalletBalance, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}
                                                </small>
                                                @if(!$canPayFullWithWallet)
                                                    <small class="d-block text-danger mt-1" style="font-size: 0.75rem;">
                                                        {{ __('website.insufficient_balance') ?? 'Insufficient balance' }}
                                                    </small>
                                                @endif
                                            </div>
                                        </label>
                                    </div>
                                @endif

                                @if($canPayWithWallet && !$canPayFullWithWallet)
                                    <!-- Mixed Payment (Wallet + Gateway) -->
                                    <div class="visaOne_Crelative">
                                        <input type="radio"
                                               class="absRadio_check payment-method-radio"
                                               name="payment_method"
                                               value="mixed"
                                               id="mixed_payment"
                                               data-payment-type="mixed"
                                               data-pay-type="knet">
                                        <label for="mixed_payment" class="visaOne_cardN">
                                            <img src="{{ asset('website/assets/img/wallet-epayment.png') }}" alt="Wallet + Online Payment" class="visaOne_cIMG" onerror="this.src='{{ asset('website/assets/img/w.png') }}'">
                                            <div class="visaOne_name"> {{ __('website.wallet_plus_online') }}</div>
                                        </label>
                                    </div>
                                @endif
                            </div>

                            <!-- Payment Breakdown (for mixed payment) -->
                            <div id="payment-breakdown" style="display: none;">
                                <div class="alert alert-info">
                                    <h5>{{ __('website.payment_breakdown') }}</h5>
                                    <div class="d-flex justify-content-between">
                                        <span>{{ __('website.wallet_payment') }}:</span>
                                        <strong>{{ number_format($userWalletBalance, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>{{ __('website.online_payment') }}:</span>
                                        <strong id="remaining-amount">{{ number_format(max(0, $orderTotal - $userWalletBalance), 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Summary -->
                            <div class="ordGreen_cardN1 mt-4">
                                <h3 class="asideSM_title"> {{ __('website.order_summary') }} </h3>

                                @foreach($order->items as $item)
                                    <div class="ordItem_des mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                @if($item->product)
                                                    @php
                                                        $settingModel = \App\Models\Setting::getSettingModel();
                                                        $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
                                                        $productImage = $item->product->getFirstMediaUrl('products', 'thumb');
                                                        $hasImage = !empty($productImage);
                                                    @endphp
                                                    <img src="{{ $productImage ?: $logoUrl }}"
                                                         class="{{ !$hasImage ? 'no-product-image' : '' }}"
                                                         alt="{{ $item->product->name }}"
                                                         class="rounded me-2"
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                @endif
                                                <div>
                                                    <span class="fw-bold">{{ $item->product->name ?? __('website.product') }}</span>
                                                    <br>
                                                    <small class="text-muted">{{ __('website.quantity') }}: {{ $item->quantity }}</small>
                                                </div>
                                            </div>
                                            <span class="fw-bold">{{ number_format($item->total, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>
                                        </div>

                                        @if($item->addons && $item->addons->count())
                                            <div class="ms-5 mt-1">
                                                @foreach($item->addons as $addon)
                                                    <small class="text-muted d-block">+ {{ $addon->name }} ({{ number_format($addon->price, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }})</small>
                                                @endforeach
                                            </div>
                                        @endif
                                        <hr class="my-2">
                                    </div>
                                @endforeach

                                <!-- Totals -->
                                <div class="DPick_ftotal">
                                    <span>{{ __('website.subtotal') }}</span>
                                    <span>{{ number_format($order->total - ($order->delivery_cost ?? 0) - ($order->discount_amount ?? 0), 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>
                                </div>

                                @if($order->order_type == 'delivery')
                                    <div class="DPick_ftotal">
                                        <span>{{ __('website.delivery_cost') }}</span>
                                        <span>
                                            @if($order->delivery_cost > 0)
                                                {{ number_format($order->delivery_cost, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}
                                            @else
                                                <span style="color: #28a745; font-weight: bold;">{{ __('website.free_delivery') }}</span>
                                            @endif
                                        </span>
                                    </div>
                                @endif

                                @if($order->discount_amount > 0)
                                    <div class="DPick_ftotal text-success">
                                        <span>{{ __('website.discount') }} ({{ $order->coupon_code }})</span>
                                        <span>-{{ number_format($order->discount_amount, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>
                                    </div>
                                @endif

                                <div class="DPick_ftotal" style="font-size: 1.2rem; font-weight: bold;">
                                    <span>{{ __('website.total') }}</span>
                                    <span>{{ number_format($order->total, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>
                                </div>

                                <!-- Order Info -->
                                <hr>
                                <h3 class="asideSM_title mt-3">
                                    {{ $order->order_type == 'pick_up' ? __('website.pick_up_info') : __('website.delivery_info') }}
                                </h3>

                                @if($order->order_type == 'delivery')
                                    @if($order->guest_address || ($order->user && $order->user->address))
                                        <div class="ordGreen_list">
                                            <img src="{{ asset('website/assets/img/house-2.svg') }}" alt="" class="ordG_icoN">
                                            <span> {{ $order->guest_address ?? $order->user->address }} </span>
                                        </div>
                                    @endif
                                @elseif($order->order_type == 'pick_up' && $order->branch)
                                    <div class="ordGreen_list">
                                        <img src="{{ asset('website/assets/img/house-2.svg') }}" alt="" class="ordG_icoN">
                                        <span> {{ $order->branch->name }} </span>
                                    </div>
                                @endif

                                <div class="ordGreen_list">
                                    <img src="{{ asset('website/assets/img/clock2.svg') }}" alt="" class="ordG_icoN">
                                    <span>
                                        @if($order->meal_type == 'asap')
                                            {{ __('website.as_soon_as_possible') }}
                                        @elseif($order->meal_type == 'scheduled')
                                            {{ __('website.scheduled') }}
                                        @elseif($order->meal_type == 'dine_in')
                                            {{ __('website.dine_in') }}
                                        @else
                                            {{ __('website.as_soon_as_possible') }}
                                        @endif
                                    </span>
                                    @if($order->scheduled_date && $order->meal_type == 'scheduled')
                                        <small class="d-block ms-4">{{ $order->scheduled_date }} {{ $order->scheduled_time }}</small>
                                    @endif
                                </div>

                                <div class="ordGreen_list">
                                    <img src="{{ asset('website/assets/img/profile.svg') }}" alt="" class="ordG_icoN">
                                    <div>
                                        <span class="d-block"> {{ $order->user_id ? $order->user->name : $order->guest_name }} </span>
                                        <a class="d-block"> {{ $order->user_id ? $order->user->phone : $order->guest_phone }} </a>
                                        <a class="d-block"> {{ $order->user_id ? $order->user->email : $order->guest_email }} </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                                class="main_bttn check_bttn w-100 hvr-sweep-to-right mrgTop_wide"
                                id="pay-button">
                            <i class="fa fa-credit-card me-2"></i>
                            {{ __('website.next') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('website-footer')
    <script>
        $(document).ready(function() {
            // Prevent clicking on disabled wallet payment
            $('#wallet_payment:disabled').closest('.visaOne_Crelative').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                AppSwal.warning('{{ __("website.wallet_balance_less_than_total") ?? "Your wallet balance is less than the order total. Please choose another payment method." }}', '{{ __("website.insufficient_balance") ?? "Insufficient Balance" }}');
                return false;
            });

            // Show payment breakdown for mixed payment and update pay_type
            $('input[name="payment_method"]:not(:disabled)').on('change', function() {
                const selectedMethod = $(this).val();
                const payType = $(this).data('pay-type') || 'knet';

                // Update hidden input for pay_type
                $('#pay_type_input').val(payType);

                if (selectedMethod === 'mixed') {
                    $('#payment-breakdown').slideDown();
                } else {
                    $('#payment-breakdown').slideUp();
                }

                // Handle payment method specific logic
                if (selectedMethod === 'applepay') {
                    // Apple Pay payment
                    console.log('Apple Pay selected');
                } else if (selectedMethod === 'knet' || selectedMethod === 'credit' || selectedMethod === 'amex') {
                    // Payment gateway methods
                    console.log('Payment method selected:', selectedMethod, 'Pay Type:', payType);
                }
            });

            // Trigger change on page load
            $('input[name="payment_method"]:checked:not(:disabled)').trigger('change');
        });
    </script>
@endsection
