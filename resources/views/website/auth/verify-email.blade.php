@extends('website.layout.master')
@section('title', __('website.verify_email') ?? 'Verify Email')

@section('website-head')
    <link rel="stylesheet" href="{{ asset('website/assets/css/auth_redesign.css') }}">
@endsection

@section('website-main')
    @php
        $settingModel = \App\Models\Setting::first();
        $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
    @endphp
    <!-- Content -->
    <section class="login_section">
        <div class="w-100 d-flex justify-content-center">
            <div class="login_wrap">
                <a href="{{ route('website.home') }}" class="inside_logo">
                    <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::getValue('site_name', app()->getLocale(), 'Run2Diet') }}">
                </a>
                <h3 class="login_title mb-3">
                    <span class="login_titleText">{{ __('website.verify_email') ?? 'Verify Email' }}</span>
                </h3>
                <p class="login_desc mbttom_50 text-center">
                    {{ __('website.verify_email_message') ?? 'Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.' }}
                </p>

                @if (session('status') == 'verification-link-sent' || session('status') == __('website.verification_link_sent'))
                    <div class="alert alert-success mb-4">
                        {{ __('website.verification_link_sent_message') ?? 'A new verification link has been sent to the email address you provided during registration.' }}
                    </div>
                @endif

                <div class="lgoin_form">
                    <form method="POST" action="{{ route('website.verification.send') }}">
                        @csrf
                        <button type="submit" class="main_bttn login_bttn w-100 hvr-sweep-to-right mbttom_20">
                            {{ __('website.resend_verification_email') ?? 'Resend Verification Email' }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('website.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-link text-muted w-100">
                            {{ __('website.logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
