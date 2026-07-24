@extends('website.layout.master')
@section('title', __('website.verify_otp'))

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
        <form action="{{ route('website.forget_pass.otp.verify.post') }}" method="post" class="w-100 d-flex justify-content-center">
            @csrf
            <div class="login_wrap">
                <a href="{{ route('website.home') }}" class="inside_logo"> 
                    <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::getValue('site_name', app()->getLocale(), 'Run2Diet') }}"> 
                </a>
                <h3 class="login_title mb-3"> <span class="login_titleText">{{ __('website.verify_otp') }}</span> </h3>
                <p class="login_desc mbttom_50 text-center"> {{ __('website.verify_otp_sent_to_phone') ?? 'Please enter the OTP code sent to your phone via SMS' }} </p>
                <div class="lgoin_form">
                    @if(session('success'))
                        <div class="alert alert-success mb-3">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <label for="otp_code" class="login_label"> {{ __('website.otp_code') }} </label>
                    <div class="form_group mbttom_40">
                        <input id="otp_code" 
                               class="login_input" 
                               type="text" 
                               name="otp_code" 
                               maxlength="6" 
                               pattern="[0-9]{6}" 
                               required 
                               autocomplete="off"
                               placeholder="{{ __('website.enter_otp_code') }}">
                        <x-input-error :messages="$errors->get('otp_code')" class="mt-2"/>
                    </div>
                    <button type="submit" class="main_bttn login_bttn w-100 hvr-sweep-to-right mbttom_20"> {{ __('website.verify') }} </button>
                    <div class="have_account"> 
                        <a href="{{ route('website.forget_pass') }}">{{ __('website.back_to_forget_password') }}</a>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection
