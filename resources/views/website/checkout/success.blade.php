@extends('website.layout.master')
@section('title', __('website.order_success'))
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
                        {{ __('website.order_success') }}
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Success Section -->
    <section class="pickup_section secPadding">
        <div class="container">
            <div class="row">
                <div class="col-12 col-lg-8 mx-auto">
                    <!-- Success Icon -->
                    <div class="text-center mb-5">
                        <div class="success-icon-wrapper" style="margin: 0 auto; width: 100px; height: 100px; border-radius: 0; background: var(--dark-matte); display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-check" style="font-size: 40px; color: var(--primary-yellow);"></i>
                        </div>
                        <h2 class="mt-4 mb-2" style="font-family: var(--artisanal-font); font-size: 36px;">{{ __('website.order_placed_successfully') }}</h2>
                        <p class="text-muted" style="letter-spacing: 1px; text-transform: uppercase; font-size: 13px;">{{ __('website.thank_you_for_order') }}</p>
                    </div>

                    <!-- Order Details Card -->
                    <div class="ordGreen_cardN1">
                        <div class="text-center mb-3">
                            <h3 class="asideSM_title">{{ __('website.order_number') }}</h3>
                            <h2 class="text-primary">{{ $order->order_number }}</h2>
                        </div>
                        
                        <hr>
                        
                        <!-- Order Items -->
                        <h4 class="asideSM_title mb-3"> {{ __('website.order_items') }} </h4>
                        @foreach($order->items as $item)
                            <div class="d-flex justify-content-between align-items-center mb-3">
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
                                             class="rounded me-3"
                                             style="width: 60px; height: 60px; object-fit: cover;">
                                    @endif
                                    <div>
                                        <strong>{{ $item->product->name ?? __('website.product') }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ __('website.quantity') }}: {{ $item->quantity }} 
                                            × {{ number_format($item->price, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}
                                        </small>
                                        @if($item->addons && $item->addons->count())
                                            <div class="ms-3 mt-1">
                                                @foreach($item->addons as $addon)
                                                    <small class="text-muted d-block">+ {{ $addon->name }}</small>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <span class="fw-bold">{{ number_format($item->total, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>
                            </div>
                            <hr>
                        @endforeach
                        
                        <!-- Payment Summary -->
                        <h4 class="asideSM_title mb-3 mt-4"> {{ __('website.payment_summary') }} </h4>
                        
                        @if($order->discount_amount > 0)
                            <div class="DPick_ftotal text-success">
                                <span>{{ __('website.discount') }} ({{ $order->coupon_code }})</span>
                                <span>-{{ number_format($order->discount_amount, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>
                            </div>
                        @endif
                        
                        @if($order->wallet_amount > 0)
                            <div class="DPick_ftotal text-info">
                                <span>{{ __('website.paid_from_wallet') }}</span>
                                <span>{{ number_format($order->wallet_amount, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>
                            </div>
                        @endif
                        
                        @if($order->gateway_amount > 0)
                            <div class="DPick_ftotal text-primary">
                                <span>{{ __('website.paid_online') }}</span>
                                <span>{{ number_format($order->gateway_amount, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>
                            </div>
                        @endif
                        
                        <div class="DPick_ftotal" style="font-size: 1.3rem; font-weight: bold; border-top: 2px solid #ddd; padding-top: 15px; margin-top: 15px;">
                            <span>{{ __('website.total_paid') }}</span>
                            <span class="text-success">{{ number_format($order->total, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>
                </div>
                        
                        <!-- Order Info -->
                        <hr>
                        <h4 class="asideSM_title mt-3"> 
                            {{ $order->order_type == 'pick_up' ? __('website.pick_up_info') : __('website.delivery_info') }} 
                        </h4>
                        
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

                    <!-- Action Buttons -->
                    <div class="text-center mt-4 d-flex">
                        <a href="{{ route('website.home') }}" class="main_bttn hvr-sweep-to-right me-2">
                            <i class="fa fa-home me-2"></i>
                    {{ __('website.back_to_home') }}
                </a>
                        @auth('web')
                            <a href="{{ route('profile.orders') }}" class="main_bttn white_bttn hvr-sweep-to-right">
                                <i class="fa fa-list me-2"></i>
                                {{ __('website.view_orders') }}
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
