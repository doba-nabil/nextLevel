@extends('website.layout.master')
@section('title', __('website.verify_otp'))

@section('website-head')
    <link rel="stylesheet" href="{{ asset('website/assets/css/auth_redesign.css') }}">
    <link rel="stylesheet" href="{{ asset('website/assets/css/website-custom.css') }}">
@endsection

@section('website-main')
    @php
        $settingModel = \App\Models\Setting::first();
        $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
    @endphp
    <!-- Content -->
    <section class="login_section">
        <form action="{{ route('website.otp.verify.post', $user_email) }}" method="post" class="w-100 d-flex justify-content-center">
            @csrf
            <div class="login_wrap">
                <a href="{{ route('website.home') }}" class="inside_logo"> 
                    <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::getValue('site_name', app()->getLocale(), 'Run2Diet') }}"> 
                </a>
                <h3 class="login_title mb-3"> <span class="login_titleText">{{ __('website.verify_otp') }}</span> </h3>
                <p class="login_desc mbttom_50 text-center"> {{ __('website.verify_otp_sent_to_mail') }} </p>
                
                @if(isset($show_otp) && $show_otp && isset($otp_code))
                <div class="alert alert-info mb-3" style="border-left: 4px solid #f6d814; background: #fffdf0; padding: 25px; text-align: center;">
                    <div style="font-family: 'Playfair Display', serif; font-style: italic; color: #888; margin-bottom: 10px;">{{ __('website.your_otp_code') }}</div>
                    <div style="font-size: 42px; font-weight: 800; color: #1a1a1a; letter-spacing: 12px; margin-bottom: 5px;">{{ $otp_code }}</div>
                    <small class="text-muted" style="letter-spacing: 1px; font-weight: 600; opacity: 0.6;">{{ __('website.this_otp_is_for_testing_only') }}</small>
                </div>
                @endif
                
                <div class="lgoin_form">
                    <label for="otp_code" class="login_label"> {{ __('website.otp_code') }}  </label>
                    <div class="form_group mbttom_40">
                        <input
                            id="otp_code"
                            name="otp_code"
                            type="text"
                            class="login_input"
                            placeholder="******"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            inputmode="numeric"
                            value="{{ isset($otp_code) && isset($show_otp) && $show_otp ? $otp_code : '' }}"
                            required
                        >
                        <i class="code_icon absinput_icon"></i>
                    </div>
                    <button type="submit" class="main_bttn login_bttn w-100 hvr-sweep-to-right mbttom_20">  {{ __('website.verify_account') }} </button>
                    
                    <br>
                    <button type="button" id="resend-otp" class="main_bttn login_bttn w-100 hvr-sweep-to-right">
                        {{ __('website.resend_otp') }}
                    </button>

                    <p id="resend-message" class="text-center mt-2 text-success" style="display:none;">
                        {{ __('website.otp_sent_again') }}
                    </p>
                </div>
            </div>
        </form>
    </section>
@endsection
