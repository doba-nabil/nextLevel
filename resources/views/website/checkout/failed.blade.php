@extends('website.layout.master')
@section('title', __('website.payment_failed'))
@section('body', true)

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
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ __('website.payment_failed') }}
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Failed Section -->
    <section class="pickup_section secPadding">
        <div class="container">
            <div class="row">
                <div class="col-12 col-lg-8 mx-auto">
                    <!-- Failed Icon -->
                    <div class="text-center mb-5">
                        <div class="failed-icon-wrapper" style="margin: 0 auto; width: 100px; height: 100px; border-radius: 0; background: var(--dark-matte); display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-times" style="font-size: 40px; color: #ff4d4d;"></i>
                        </div>
                        <h2 class="mt-4 mb-2 text-danger" style="font-family: var(--artisanal-font); font-size: 36px;">{{ __('website.payment_failed') }}</h2>
                        <p class="text-muted" style="letter-spacing: 1px; text-transform: uppercase; font-size: 13px;">{{ __('website.payment_failed_message') }}</p>
                    </div>    
                        @if(session('error'))
                            <div class="alert alert-danger mt-3">
                                <strong>{{ __('website.error_details') }}:</strong><br>
                                {{ session('error') }}
                            </div>
                        @endif
                        
                        @if($order->payment_response)
                            @php
                                $paymentResponse = json_decode($order->payment_response, true);
                            @endphp
                            @if(isset($paymentResponse['error']))
                                <div class="alert alert-warning mt-3">
                                    <strong>{{ __('website.payment_error') }}:</strong><br>
                                    {{ $paymentResponse['error'] }}
                                    @if(isset($paymentResponse['timestamp']))
                                        <br><small class="text-muted">{{ $paymentResponse['timestamp'] }}</small>
                                    @endif
                                </div>
                            @elseif(isset($paymentResponse['status']))
                                <div class="alert alert-info mt-3">
                                    <strong>{{ __('website.payment_status') }}:</strong> {{ $paymentResponse['status'] }}
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Order Details Card -->
                    <div class="ordGreen_cardN1">
                        <div class="text-center mb-3">
                            <h3 class="asideSM_title">{{ __('website.order_number') }}</h3>
                            <h2 class="text-muted">{{ $order->order_number }}</h2>
                            <span class="badge bg-danger">{{ __('website.payment_pending') }}</span>
                        </div>
                        
                        <hr>
                        
                        <!-- Order Summary -->
                        <h4 class="asideSM_title mb-3"> {{ __('website.order_summary') }} </h4>
                        
                        <div class="DPick_ftotal">
                            <span>{{ __('website.order_total') }}</span>
                            <span class="fw-bold">{{ number_format($order->total, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>
                        </div>
                        
                        @if($order->payment_method)
                            <div class="DPick_ftotal mt-2">
                                <span>{{ __('website.payment_method') }}</span>
                                <span class="text-capitalize">{{ ucfirst($order->payment_method) }}</span>
                            </div>
                        @endif
                        
                        @if($order->payment_status)
                            <div class="DPick_ftotal mt-2">
                                <span>{{ __('website.payment_status') }}</span>
                                <span class="badge bg-danger">{{ ucfirst($order->payment_status) }}</span>
                            </div>
                        @endif
                        
                        @if($order->wallet_amount > 0 && $order->payment_method === 'mixed')
                            <div class="alert alert-success mt-3">
                                <strong><i class="fa fa-check-circle"></i> {{ __('website.good_news') }}:</strong> 
                                <br>{{ __('website.wallet_not_deducted') }} 
                                <br>{{ __('website.intended_wallet_amount') }}: {{ number_format($order->wallet_amount, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}
                                <br>{{ __('website.your_wallet_balance_safe') }}
                            </div>
                        @elseif($order->wallet_amount > 0)
                            <div class="alert alert-info mt-3">
                                <strong>{{ __('website.note') }}:</strong> 
                                {{ number_format($order->wallet_amount, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }} {{ __('website.deducted_from_wallet') }}
                            </div>
                        @endif
                        
                        @if($order->payment_id)
                            <div class="mt-2">
                                <small class="text-muted">{{ __('website.payment_id') }}: {{ $order->payment_id }}</small>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="text-center mt-4 d-flex justify-space-between">
                        <a href="{{ route('website.orders.payment', $order->id) }}" class="main_bttn hvr-sweep-to-right me-2">
                            <i class="fa fa-redo me-2"></i>
                            {{ __('website.try_again') }}
                        </a>
                        <a href="{{ route('website.home') }}" class="main_bttn white_bttn hvr-sweep-to-right">
                            <i class="fa fa-home me-2"></i>
                            {{ __('website.back_to_home') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

