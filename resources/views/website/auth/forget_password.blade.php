@extends('website.layout.master')
@section('title', __('website.forget_password'))

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
        <form method="POST" action="{{ route('website.forget_pass.post') }}" class="w-100 d-flex justify-content-center">
            @csrf
            <div class="login_wrap">
                <a href="{{ route('website.home') }}" class="inside_logo"> 
                    <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::getValue('site_name', app()->getLocale(), 'Run2Diet') }}"> 
                </a>
                <h3 class="login_title mb-3"> <span class="login_titleText">{{ __('website.forget_password') }}</span> </h3>
                <p class="login_desc mbttom_50 text-center"> {{ __('website.enter_phone_for_password_reset') ?? 'Enter your phone number to reset your password' }}</p>
                <div class="lgoin_form">
                    <label for="phone" class="login_label"> {{ __('website.mobile_no') }} </label>
                    <div class="form_group mbttom_30 position-relative">
                        <div class="d-flex">
                            @php
                                $countries = \App\Models\Location::where('type', 'country')->where('active', true)->orderBy('id')->get();
                                $firstCountry = $countries->first();
                                $selectedCountryId = old('country_id', $firstCountry ? $firstCountry->id : '');
                            @endphp
                            <select name="country_id" id="forget_country_code" class="form-select" style="width: 120px; border-right: none;" required>
                                <option value="">{{ __('website.select_country') }}</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" data-phone-code="{{ $country->phone_code }}" {{ $selectedCountryId == $country->id ? 'selected' : '' }}>
                                        {{ $country->phone_code }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="tel" name="phone" value="{{ old('phone') }}" id="forget_phone" class="login_input" placeholder="99999999" maxlength="8" style="flex: 1;" required>
                        </div>
                        <i class="fa-solid fa-phone absinput_icon" style="right: 10px;"></i>
                        <x-input-error :messages="$errors->get('phone')" class="mt-2"/>
                        <x-input-error :messages="$errors->get('country_id')" class="mt-2"/>
                    </div>
                    <button type="submit" class="main_bttn login_bttn w-100 hvr-sweep-to-right mbttom_20">  {{ __('website.reset_password') }}</button>
                    <div class="have_account"> {{ __('website.otp_will_be_sent_to_phone') ?? 'OTP will be sent to your phone via SMS' }}</div>
                </div>
            </div>
        </form>
    </section>
@endsection
