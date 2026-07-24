@extends('website.layout.master')
@section('title', __('website.wallet_topup_failed'))
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
                        {{ __('website.wallet_topup_failed') }}
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
                    <div class="text-center mb-4">
                        <div class="failed-icon-wrapper" style="margin: 0 auto; width: 120px; height: 120px; border-radius: 50%; background: #dc3545; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-times" style="font-size: 60px; color: white;"></i>
                        </div>
                        <h2 class="mt-4 mb-2 text-danger">{{ __('website.payment_failed') }}</h2>
                        <p class="text-muted">{{ __('website.wallet_topup_failed_message') }}</p>
                        
                        @if(session('error'))
                            <div class="alert alert-danger mt-3">
                                <strong>{{ __('website.error_details') }}:</strong><br>
                                {{ session('error') }}
                            </div>
                        @endif
                    </div>

                    <!-- Transaction Details Card -->
                    <div class="ordGreen_cardN1">
                        <div class="text-center mb-3">
                            <h3 class="asideSM_title">{{ __('website.transaction_details') }}</h3>
                        </div>
                        
                        <hr>
                        
                        <!-- Attempted Amount -->
                        @if($amount > 0)
                            <div class="DPick_ftotal">
                                <span>{{ __('website.attempted_amount') }}</span>
                                <span class="fw-bold">{{ number_format((float)$amount, 3) }} {{ session('currency', 'KD') }}</span>
                            </div>
                        @endif
                        
                        <div class="DPick_ftotal mt-2">
                            <span>{{ __('website.payment_method') }}</span>
                            <span>{{ __('website.my_fatoorah') }}</span>
                        </div>
                        
                        <div class="DPick_ftotal mt-2">
                            <span>{{ __('website.status') }}</span>
                            <span class="badge bg-danger">{{ __('website.failed') }}</span>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fa fa-info-circle me-2"></i>
                            <strong>{{ __('website.good_news') }}:</strong> 
                            {{ __('website.no_money_deducted') }}
                        </div>
                    </div>
                    
                    <!-- What to do next -->
                    <div class="alert alert-warning mt-3">
                        <h5><i class="fa fa-exclamation-triangle me-2"></i>{{ __('website.what_to_do_next') }}</h5>
                        <ul class="mb-0">
                            <li>{{ __('website.check_payment_details') }}</li>
                            <li>{{ __('website.ensure_sufficient_balance') }}</li>
                            <li>{{ __('website.try_different_card') }}</li>
                            <li>{{ __('website.contact_support_if_persists') }}</li>
                        </ul>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="text-center mt-4">
                        <a href="{{ route('profile.index') }}?tab=add_money" class="main_bttn hvr-sweep-to-right me-2">
                            <i class="fa fa-redo me-2"></i>
                            {{ __('website.try_again') }}
                        </a>
                        <a href="{{ route('profile.index') }}" class="main_bttn white_bttn hvr-sweep-to-right">
                            <i class="fa fa-user me-2"></i>
                            {{ __('website.back_to_profile') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection






















