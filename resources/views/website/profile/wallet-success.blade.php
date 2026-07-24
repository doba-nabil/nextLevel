@extends('website.layout.master')
@section('title', __('website.wallet_topup_successful'))
@section('body', true)

@section('website-main')
    <!-- BreadCrumb -->
    <div class="breadCrumb_section midPadding">
        <div class="container px-lg-0">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('website.home') }}">{{ __('website.home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('profile.index') }}">{{ __('website.profile') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ __('website.wallet_topup_successful') }}
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
                    <div class="text-center mb-4">
                        <div class="success-icon-wrapper" style="margin: 0 auto; width: 120px; height: 120px; border-radius: 50%; background: #f6d814; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-check" style="font-size: 60px; color: white;"></i>
                        </div>
                        <h2 class="mt-4 mb-2 text-success">{{ __('website.payment_successful') }}</h2>
                        <p class="text-muted">{{ __('website.wallet_topup_success_message') }}</p>
                        
                        @if(session('success'))
                            <div class="alert alert-success mt-3">
                                {{ session('success') }}
                            </div>
                        @endif
                    </div>

                    <!-- Transaction Details Card -->
                    <div class="ordGreen_cardN1">
                        <div class="text-center mb-3">
                            <h3 class="asideSM_title">{{ __('website.transaction_details') }}</h3>
                        </div>
                        
                        <hr>
                        
                        <!-- Top-up Amount -->
                        <div class="DPick_ftotal">
                            <span>{{ __('website.amount_added') }}</span>
                            <span class="fw-bold text-success">
                                + {{ number_format((float)$topupAmount, 3) }} {{ session('currency', 'KD') }}
                            </span>
                        </div>
                        
                        <div class="DPick_ftotal mt-2">
                            <span>{{ __('website.payment_method') }}</span>
                            <span>{{ __('website.my_fatoorah') }}</span>
                        </div>
                        
                        <hr>
                        
                        <!-- New Balance -->
                        <div class="DPick_ftotal mt-3" style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                            <span class="fw-bold">{{ __('website.current_wallet_balance') }}</span>
                            <span class="fw-bold" style="font-size: 1.3em; color: #f6d814;">
                                {{ number_format((float)$walletBalance, 3) }} {{ session('currency', 'KD') }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="text-center mt-4">
                        <a href="{{ route('profile.index') }}" class="main_bttn hvr-sweep-to-right me-2">
                            <i class="fa fa-wallet me-2"></i>
                            {{ __('website.view_wallet') }}
                        </a>
                        <a href="{{ route('website.home') }}" class="main_bttn white_bttn hvr-sweep-to-right">
                            <i class="fa fa-shopping-bag me-2"></i>
                            {{ __('website.continue_shopping') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection






















