@extends('website.layout.master')
@section('title', __('website.sign_up'))

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
        <form method="POST" action="{{ route('website.register.post') }}" class="w-100 d-flex justify-content-center">
            @csrf
            <div class="login_wrap">
                <a href="{{ route('website.home') }}" class="inside_logo">
                    <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::getValue('site_name', app()->getLocale(), 'Run2Diet') }}">
                </a>
                <h3 class="login_title"> <span class="login_titleText">{{ __('website.sign_up') }}</span> </h3>
                <div class="lgoin_form">
                    <label for="name" class="login_label"> {{ __('website.name') }} </label>
                    <div class="form_group mbttom_30">
                        <input type="text" name="name" value="{{ old('name') }}" class="login_input" placeholder="abc">
                        <i class="name_icon absinput_icon"></i>
                        <x-input-error :messages="$errors->get('name')" class="mt-2"/>
                    </div>
                    <label for="phone" class="login_label"> {{ __('website.mobile_no') }} </label>
                    <div class="form_group mbttom_30 position-relative">
                        <div class="d-flex">
                            @php
                                $countries = \App\Models\Location::where('type', 'country')->where('active', true)->orderBy('id')->get();
                                $firstCountry = $countries->first();
                                $selectedCountryId = old('country_id', $firstCountry ? $firstCountry->id : '');
                            @endphp
                            <select name="country_id" id="country_code" class="form-select" style="width: 120px; border-right: none;">
                                <option value="">{{ __('website.select_country') }}</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" data-phone-code="{{ $country->phone_code }}" {{ $selectedCountryId == $country->id ? 'selected' : '' }}>
                                        {{ $country->phone_code }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="tel" name="phone" value="{{ old('phone') }}" id="phone" class="login_input" placeholder="99999999" maxlength="8" style="flex: 1;">
                        </div>
                        <x-input-error :messages="$errors->get('phone')" class="mt-2"/>
                        <x-input-error :messages="$errors->get('country_id')" class="mt-2"/>
                    </div>
                    <label for="email" class="login_label"> {{ __('website.email') }} <span class="text-muted small">({{ __('website.optional') ?? 'Optional' }})</span> </label>
                    <div class="form_group mbttom_30">
                        <input type="email" name="email" value="{{ old('email') }}" class="login_input" placeholder="abc@gmail.com">
                        <i class="email_icon absinput_icon"></i>
                        <x-input-error :messages="$errors->get('email')" class="mt-2"/>
                    </div>
                    <div class="form_group mbttom_30">
                        <input type="password" name="password" class="login_input" value=""
                               placeholder="{{ __('website.password') }}" id="form_password">
                        <span class="toggle-password absinput_icon">
                            <i class="passToggle_icon fa-solid fa-eye-slash"></i>
                            <i class="passToggle_icon fa-solid fa-eye"></i>
                        </span>
                        <x-input-error :messages="$errors->get('password')" class="mt-2"/>
                    </div>
                    <div class="have_account">
                        <span> {{ __('website.by_signing_up_you_agree_with_the') }} <a
                                href="privacy.html"> {{ __('website.privacy_policy') }} </a> {{ __('website.and') }} <a
                                href="terms.html"> {{ __('website.terms_of_healthybite') }} </a> </span>
                    </div>
                    <button type="submit"
                            class="main_bttn login_bttn w-100 hvr-sweep-to-right mbttom_20"> {{ __('website.sign_up') }} </button>
                    <div class="have_account d-none"> {{ __('website.or_continue_with') }} </div>
                    <div class="buttons_wrapper justify-content-center w-100 mbttom_40  d-none">
                        <a href="{{ route('website.google.login') }}" class="appG_link">
                            <img src="{{ asset('website') }}/assets/img/go.svg" alt="Google" class="appG_icon">
                        </a>
                    </div>
                    <div class="have_account">
                        <span> {{ __('website.have_an_account') }} <a
                                href="{{ route('website.login') }}"> {{ __('website.sign_in') }} </a> </span>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection
