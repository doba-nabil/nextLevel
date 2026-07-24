@extends('website.layout.master')
@section('title', __('website.reset_password'))

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
        <form method="POST" action="{{ route('password.update') }}" class="w-100 d-flex justify-content-center">
            @csrf
            <div class="login_wrap">
                <a href="{{ route('website.home') }}" class="inside_logo"> 
                    <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::getValue('site_name', app()->getLocale(), 'Run2Diet') }}"> 
                </a>
                <h3 class="login_title"> <span class="login_titleText">{{ __('website.reset_password') }}</span> </h3>
                <input value="{{ request('token', null) }}" hidden="" name="token"/>
                <div class="lgoin_form">
                    <x-input-label class="login_label" for="email" :value="__('website.email_or_phone') ?? 'Email or Phone'"/>
                    <div class="form_group mbttom_40">
                        <x-text-input id="email" placeholder="{{ session('email') ? session('email') : 'Email or Phone' }}"
                                      class="login_input" type="text" name="email"
                                      :value="old('email', session('email'))" required autofocus autocomplete="username"/>
                        <i class="email_icon absinput_icon"></i>
                        <x-input-error :messages="$errors->get('email')" class="mt-2"/>
                    </div>
                    <div class="form_group mbttom_6">
                        <x-text-input id="form_password" class="login_input"
                                      type="password"
                                      name="password"
                                      placeholder="{{ __('website.password') }}"
                                      required autocomplete="current-password"/>
                        <span class="toggle-password absinput_icon">
                            <i class="passToggle_icon fa-solid fa-eye-slash"></i>
                            <i class="passToggle_icon fa-solid fa-eye"></i>
                        </span>
                        <x-input-error :messages="$errors->get('password')" class="mt-2"/>
                    </div>
                    <div class="form_group mbttom_6">
                        <x-text-input id="password_confirmation" class="login_input"
                                      type="password"
                                      name="password_confirmation"
                                      placeholder="{{ __('website.password_confirmation') }}"
                                      required autocomplete="current-password"/>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2"/>
                    </div>
                    <button type="submit" class="main_bttn login_bttn w-100 hvr-sweep-to-right mbttom_20">
                        {{ __('website.reset_password') }}
                    </button>
                </div>
            </div>
        </form>
    </section>
@endsection
