@extends('website.layout.master')
@section('title', __('website.login'))

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
        <form method="POST" action="{{ route('website.login.post') }}" class="w-100 d-flex justify-content-center">
            @csrf
            <div class="login_wrap">
                <a href="{{ route('website.home') }}" class="inside_logo">
                    <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::getValue('site_name', app()->getLocale(), 'Run2Diet') }}">
                </a>
                <h3 class="login_title"> <span class="login_titleText">{{ __('website.login') }}</span> </h3>

                <div class="lgoin_form">
                    <x-input-label class="login_label" for="login" :value="__('website.email_or_phone') ?? 'Email or Phone'"/>
                    <div class="form_group mbttom_30 position-relative">
                        <div id="phoneLoginWrapper" style="display: none;">
                            <select name="country_id" id="login_country_code" class="form-select d-none" style="display: none !important;">
                                <option value="">{{ __('website.select_country') }}</option>
                                @foreach(\App\Models\Location::where('type', 'country')->where('active', true)->orderBy('name')->get() as $country)
                                    <option value="{{ $country->id }}" data-phone-code="{{ $country->phone_code }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                        {{ $country->phone_code }}
                                    </option>
                                @endforeach
                            </select>
                            <x-text-input id="login" placeholder="{{ __('website.email_or_phone') }}"
                                          class="login_input" type="tel" name="login"
                                          :value="old('login')" maxlength="8"/>
                            <i class="fa-solid fa-phone absinput_icon"></i>
                        </div>
                        <div id="emailLoginWrapper">
                            <x-text-input id="login" placeholder="{{ __('website.email_or_phone') }}"
                                          class="login_input" type="text" name="login"
                                          :value="old('login')" autofocus autocomplete="username"/>
                            <i class="email_icon absinput_icon"></i>
                        </div>
                        <x-input-error :messages="$errors->get('login')" class="mt-2"/>
                        <x-input-error :messages="$errors->get('country_id')" class="mt-2"/>
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
                    <a href="{{ route('website.forget_pass') }}" class="forgetPass_link"> {{ __('website.Forget password?') }} </a>
                    <button type="submit" class="main_bttn login_bttn w-100 hvr-sweep-to-right mbttom_20">
                        {{ __('website.login') }}
                    </button>
                    <div class="have_account d-none"> {{ __('website.or_continue_with') }} </div>
                    <div class="buttons_wrapper justify-content-center w-100 mbttom_40  d-none">
                        <a href="{{ route('website.google.login') }}" class="appG_link">
                            <img src="{{ asset('website') }}/assets/img/go.svg" alt="Google" class="appG_icon">
                        </a>
                    </div>
                    <div class="have_account">
                        <span> {{ __('website.have_an_account') }} <a
                                href="{{ route('website.register') }}"> {{ __('website.sign_up') }} </a> </span>
                        <span> {{ __('website.or') }} </span>
                        <span> <a href="{{ route('website.home') }}" class="text-primary"> {{ __('website.continue_as_guest') }} </a> </span>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection

@section('website-footer')
    <script>
        $(document).ready(function() {
            const $loginInput = $('#login');
            const $phoneWrapper = $('#phoneLoginWrapper');
            const $emailWrapper = $('#emailLoginWrapper');
            const $countrySelect = $('#login_country_code');
            
            function detectInputType(value) {
                if (!value) return 'email';
                
                // Check if it's an email
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (emailPattern.test(value)) {
                    return 'email';
                }
                
                // Check if it's a phone (only digits, 8 digits)
                const phonePattern = /^[0-9]{8}$/;
                const cleanValue = value.replace(/[^0-9]/g, '');
                if (phonePattern.test(cleanValue) || cleanValue.length <= 8) {
                    return 'phone';
                }
                
                // Default to email
                return 'email';
            }
            
            function switchToPhone() {
                $emailWrapper.hide();
                $phoneWrapper.show();
                $loginInput.attr('type', 'tel').attr('maxlength', '8');
                $countrySelect.removeAttr('required');
                // Move input value to phone input
                const currentValue = $loginInput.val().replace(/[^0-9]/g, '');
                $loginInput.val(currentValue);
                $loginInput.attr('placeholder', '{{ __("website.email_or_phone") }}');
            }
            
            function switchToEmail() {
                $phoneWrapper.hide();
                $emailWrapper.show();
                $loginInput.attr('type', 'text').removeAttr('maxlength');
                $countrySelect.removeAttr('required');
                $loginInput.attr('placeholder', '{{ __("website.email_or_phone") }}');
            }
            
            // Detect on input change
            $loginInput.on('input', function() {
                const value = $(this).val();
                const type = detectInputType(value);
                
                if (type === 'phone' && $emailWrapper.is(':visible')) {
                    switchToPhone();
                } else if (type === 'email' && $phoneWrapper.is(':visible')) {
                    switchToEmail();
                }
            });
            
            // Validate before form submit
            $('form').on('submit', function(e) {
                const value = $loginInput.val();
                const type = detectInputType(value);
                
                if (type === 'phone') {
                    // Make sure phone wrapper is visible
                    if ($emailWrapper.is(':visible')) {
                        switchToPhone();
                    }
                    
                    // Validate phone length
                    const cleanValue = value.replace(/[^0-9]/g, '');
                    if (cleanValue.length !== 8) {
                        e.preventDefault();
                        $loginInput.focus();
                        alert('{{ __("website.phone_must_be_8_digits") }}');
                        return false;
                    }
                } else if (type === 'email') {
                    // Make sure email wrapper is visible
                    if ($phoneWrapper.is(':visible')) {
                        switchToEmail();
                    }
                }
            });
            
            // Detect on page load if old input exists
            @if(old('country_id'))
                switchToPhone();
                $loginInput.val('{{ old("login") }}');
            @elseif(old('login'))
                const oldValue = '{{ old("login") }}';
                const type = detectInputType(oldValue);
                if (type === 'phone') {
                    switchToPhone();
                }
            @endif
            
            // Format phone input to only allow numbers
            $loginInput.on('keypress', function(e) {
                if ($phoneWrapper.is(':visible')) {
                    // Only allow numbers
                    if (!/[0-9]/.test(String.fromCharCode(e.which))) {
                        e.preventDefault();
                    }
                }
            });
            
            // Clean phone input on paste
            $loginInput.on('paste', function(e) {
                if ($phoneWrapper.is(':visible')) {
                    setTimeout(function() {
                        const value = $loginInput.val().replace(/[^0-9]/g, '');
                        $loginInput.val(value);
                    }, 10);
                }
            });
        });
    </script>
@endsection
