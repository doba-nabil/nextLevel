<!--loader-->
@php
    // Optimize: Use cached setting model
    $settingModel = \App\Models\Setting::getSettingModel();
    $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
@endphp
<div class="loader-container" id="loader-container">
    <div class="logo-loader-wrapper">
        <img src="{{ $logoUrl }}" alt="" class="absLoader_logo absLoader_logo-bg" loading="eager" fetchpriority="high" decoding="async">
        <img src="{{ $logoUrl }}" alt="" class="absLoader_logo absLoader_logo-fill" loading="eager" fetchpriority="high" decoding="async">
    </div>
</div>
<!--sidebar-->
<div class="mob-overlay"></div>
@unless(Route::is('website.login', 'website.register', 'website.otp.verify', 'website.forget_pass', 'password.reset'))
    <div class="sidebar-wrapper" id="mainSide_menu">
        <div class="burgerBtn">
            <i class="fa-solid fa-xmark"></i>
        </div>
        <div class="absMenu_inner desktop__none">
            <div class="container">
                <div class="contctop_row mbttom_24">
                    @auth('web')
                        <a href="{{ route('profile.index') }}" class="logFlex_wrap w-75">
                            <img src="{{ asset('website') }}/assets/img/user.svg" alt="" class="logSM_imG" loading="lazy" decoding="async">
                            <span> {{ auth('web')->user()->name ?? __('website.my_account') }} </span>
                        </a>
                        <a href="{{ route('website.logout') }}" class="mob_link asideMob_link login_SMbttn"> {{ __('website.logout') }} </a>
                    @else
                        <a href="{{ route('website.login') }}" class="logFlex_wrap w-75">
                            <img src="{{ asset('website') }}/assets/img/login.svg" alt="" class="logSM_imG" loading="lazy" decoding="async">
                            <span> {{ __('website.login_unlock_offers') }} </span>
                        </a>
                        <a href="{{ route('website.login') }}" class="mob_link asideMob_link login_SMbttn"> {{ __('website.login') }} </a>
                    @endauth
                </div>
                <div class="contctop_row align-items-end mbttom_24">
                    <div class="w-50" style="flex: 0 0 49%;">
                        @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                            @if(LaravelLocalization::getCurrentLocale() != $localeCode)
                                <a href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                                   class="mob_link asideMob_link mb-3">
                                    <img src="{{ asset('website') }}/assets/img/{{ $localeCode == 'ar' ? 'kw' : 'en' }}.png" alt="" class="userLog_icon" loading="lazy" decoding="async">
                                    <span>{{ strtoupper($localeCode) }}</span>
                                </a>
                            @endif
                        @endforeach
                        <button class="langMD_bttn" id="changeLocationBtnMobile" title="{{ __('website.change_location') }}">
                            <i class="fa-solid fa-location-dot me-2"></i><span id="location-text-mobile-sidebar">{{ __('website.your_location') }}</span>
                        </button>
                        <div id="delivery-time-mobile" class="delivery-time-display" style="display: none; font-size: 12px; color: #666; margin-top: 5px;">
                            <i class="fa-solid fa-clock me-1"></i><span id="delivery-time-text-mobile"></span>
                        </div>
                        <div class="dropdown mt-2">
                            <a class="langMD_bttn dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                               aria-expanded="false">
                                {{ optional(\App\Models\Currency::whereHas('location')->where('key', session('currency'))->first())->name }}
                            </a>
                            <ul class="dropdown-menu w-100">
                                @foreach (\App\Models\Currency::whereHas('location')->get() as $currency)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('change-currency', $currency->key) }}">
                                            {{ $currency->sign }} {{ $currency->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="w-50" style="flex: 0 0 48%; padding-inline-start: 10px;">
                        @php
                            $walletBalance = 0;
                            $userPoints = 0;
                            if(auth('web')->check()) {
                                $user = auth('web')->user();
                                $walletBalance = $user->wallet ? (float) $user->wallet->balance : 0;
                                $userPoints = $user->points ?? 0;
                            }
                        @endphp
                        <div class="sidebar-wallet-section">
                            <div class="wallet-item mb-2">
                                <div class="wallet-icon mb-1">
                                    <i class="fa-solid fa-wallet" style="font-size: 18px; color: #000;"></i>
                                </div>
                                <div class="mob_title mb-1" style="font-size: 11px; color: #666; text-transform: uppercase;">{{ __('website.wallet') ?? 'Wallet' }}</div>
                                <div class="wallet-value" style="font-size: 13px; font-weight: 600; color: #000;">
                                    {{ auth('web')->check() ? number_format($walletBalance, 3) . ' ' . \App\Models\Currency::getCurrentCurrencySign() : 'N/A' }}
                                </div>
                            </div>
                            <div class="points-item">
                                <div class="points-icon mb-1">
                                    <i class="fa-solid fa-star" style="font-size: 18px; color: #000;"></i>
                                </div>
                                <div class="mob_title mb-1" style="font-size: 11px; color: #666; text-transform: uppercase;">{{ __('website.points') }}</div>
                                <div class="points-value" style="font-size: 13px; font-weight: 600; color: #000;">
                                    {{ auth('web')->check() ? number_format($userPoints, 0) : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="contctop_row mbttom_24">
                    <ul class="asideNav_links">
                        <li>
                            <a href="{{ route('website.categories') }}"> {{ __('website.categories') }} </a>
                        </li>
                        <li>
                            <a href="{{ route('website.branches') }}"> {{ __('website.branches') }} </a>
                        </li>
                        <li>
                            <a href="{{ route('profile.index') }}?tab=track_orders" onclick="openProfileTab('track_orders')"> {{ __('website.track_order') }} </a>
                        </li>
                        <li>
                            <a href="{{ route('profile.index') }}?tab=orders" onclick="openProfileTab('orders')"> {{ __('website.order_history') }} </a>
                        </li>
                        <li>
                            <a href="{{ route('website.home') }}#offers"> {{ __('website.offers') }} </a>
                        </li>
                        <li>
                            <a href="{{ route('website.menus') }}"> {{ __('website.explore_menu') }} </a>
                        </li>
                    </ul>
                </div>
                <div class="contctop_row mbttom_24">
                    <ul class="asideNav_links">
                        <li>
                            <a href="{{ route('website.pages.show', 'privacy-policy') }}"> {{ __('website.privacy_policy') }} </a>
                        </li>
                        <li>
                            <a href="{{ route('website.pages.show', 'about-us') }}"> {{ __('website.about_us') }} </a>
                        </li>
                        <li>
                            <a href="{{ url('branches') }}"> {{ __('website.contact_us') }} </a>
                        </li>
                    </ul>
                </div>
                <div class="contctop_row mbttom_24 border-0">
                    <ul class="asideNav_links">
                        <li>
                            @php
                                $contactPhone = \App\Models\Setting::getValue('contact_phone', null, '');
                                $cleanPhone = $contactPhone ? preg_replace('/[^0-9+]/', '', $contactPhone) : '';
                            @endphp
                            @if($cleanPhone)
                                <a href="tel:{{ $cleanPhone }}"> {{ __('website.call_support') }} </a>
                            @else
                                <a href="{{ url('branches') }}"> {{ __('website.call_support') }} </a>
                            @endif
                        </li>
                    </ul>
                </div>
                 @php
                    $settings_socials = \App\Models\Setting::where('key', 'socials')->get()->pluck('value', 'key')->toArray();
                    $socialsJson = $settings_socials['socials'] ?? '[]';
                    $socials = json_decode($socialsJson, true);
                @endphp
               <div class="footer_Column pb-4">
                        <div class="social_flex">
                            @foreach($socials as $social)
                                <a href="{!! $social['url'] !!}" class="social_link">
                                    <i class="{!! $social['icon'] !!}"></i>
                                </a>
                            @endforeach

                        </div>
                    </div>
            </div>
        </div>
    </div>
    <div class="sidebar-wrapper wideSide_menu" id="sideCart_menu">
        <div class="burgerBtn">
            <i class="las la-times"></i>
        </div>
        <div class="desktopSide_menu">
            <h3 class="notSide_title"> {{ __('website.cart') }} </h3>

            <div class="cartSide_content">
                @if($cartProducts->isEmpty())
                    <p class="text-center py-4">{{ __('website.cart_empty') }}</p>
                @else
                    @foreach($cartProducts as $item)
                        <div class="product_cardN1 mb-3">
                            <a href="{{ route('website.products', $item['slug']) }}" class="prodThumb_link">
                                @php
                                    $settingModel = \App\Models\Setting::getSettingModel();
                                    $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
                                    $hasImage = !empty($item['image']);
                                @endphp
                                <img src="{{ $item['image'] ?: $logoUrl }}" alt="" class="prodThumb_img {{ !$hasImage ? 'no-product-image' : '' }}" loading="lazy" decoding="async">
                            </a>
                            <div class="content_box">
                                <h5 class="pro_title"><a
                                        href="{{ route('website.products', $item['slug']) }}">{{ $item['name'] }}</a>
                                </h5>
                                <div class="content_bInfo">
                                    <div
                                        class="pro_price">{{ $item['price'] }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</div>
                                    <div><strong>Qty:</strong> {{ $item['quantity'] }}</div>

                                    @if($item['addons']->count())
                                        <div class="addons_list mt-1">
                                            <small>+ Addons:</small>
                                            <ul class="ps-3">
                                                @foreach($item['addons'] as $addon)
                                                    <li>{{ $addon->name }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="buttons_wrapper fixbttom_bttns w-100">
                <div class="total_box text-center">
                    <strong>Total:</strong>
                    {{ number_format($cartTotal, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}
                </div>

                <a href="{{ route('cart.index') }}"
                   class="main_bttn hvr-sweep-to-right w-100">{{ __('website.continue_checkout') }}</a>
            </div>
        </div>
    </div>

    <!--start header-->
    <header class="main__header">
        <div class="header__flex">
            <div class="container px-lg-0">
                <div class="contctop_row d__mob__none">
                    <div class="buttons_wrapper gap-4">
                        <div class="dropdown">
                            <a class="langMD_bttn dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                               aria-expanded="false">
                                <img
                                    src="{{ asset('website') }}/assets/img/{{ LaravelLocalization::getCurrentLocale() == 'ar' ? 'kw' : 'en' }}.png"
                                    alt="" class="userLog_icon" loading="lazy" decoding="async">
                                {{ LaravelLocalization::getCurrentLocale() == 'ar' ? __('website.arabic') : __('website.english') }}
                            </a>
                            <ul class="dropdown-menu">
                                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    @if(LaravelLocalization::getCurrentLocale() != $localeCode)
                                        <li>
                                            <a class="dropdown-item"
                                               href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
                                                <img
                                                    src="{{ asset('website') }}/assets/img/{{ $localeCode == 'ar' ? 'kw' : 'en' }}.png"
                                                    loading="lazy" decoding="async"
                                                    alt="" class="userLog_icon">
                                                <span>{{ $localeCode == 'ar' ? __('website.arabic') : __('website.english') }}</span>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                        <div class="dropdown">
                            <a class="langMD_bttn dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                               aria-expanded="false">
                                {{ \App\Models\Currency::whereHas('location', function($e){
        $e->where('active', 1);
    })
    ->where('key', session('currency'))
    ->first()?->name ?? '---' }}
                            </a>
                            <ul class="dropdown-menu">
                                @foreach (\App\Models\Currency::whereHas('location',function($e){ $e->where('active',1); })->get() as $currency)
                                    <li><a class="dropdown-item"
                                           href="{{ route('change-currency', $currency->key) }}">{{ $currency->sign }} {{ $currency->name }} </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @php
                        $contactPhone = \App\Models\Setting::getValue('contact_phone', null, '');
                        $cleanPhone = $contactPhone ? preg_replace('/[^0-9+]/', '', $contactPhone) : '';
                    @endphp
                    @if($cleanPhone)
                        <a href="tel:{{ $cleanPhone }}"
                           class="conTop_link">
                            <i class="call_icon"></i>
                            <span> {{ __('website.call_us_now') }}: {{ $contactPhone }} </span>
                        </a>
                    @else
                        <a href="{{ url('branches') }}"
                           class="conTop_link">
                            <i class="call_icon"></i>
                            <span> {{ __('website.call_us_now') }}: {{ $contactPhone }} </span>
                        </a>
                    @endif
                </div>
                <div class="contctop_row">
                    <div class="navbar_toggler desktop__none" id="sidebar_toggler">
                        <i class="las la-bars"></i>
                    </div>
                    <a class="navbar-brand" href="{{ route('website.home') }}">
                        <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::getValue('site_name', app()->getLocale(), 'Run2Diet') }}" loading="eager" fetchpriority="high" decoding="async">
                    </a>
                    <div class="header-payment-icons d-flex align-items-center ms-3 me-3 gap-2 d__mob__none">
                        <div class="payment-icon-wrapper visa-icon" title="{{ __('website.payment_visa_available') }}">
                            <i class="fa-brands fa-cc-visa"></i>
                        </div>
                        <div class="payment-icon-wrapper cash-icon disabled" title="{{ __('website.payment_cash_unavailable') }}">
                            <i class="fa-solid fa-money-bill-1-wave"></i>
                            <span class="unavailable-mark">x</span>
                        </div>
                    </div>
                    <div class="contctop_row w-100 p-0 py-3 desktop__none">
                        <div>
                            <h3 class="mob_title">{{ __('website.select_your_location') }}</h3>
                            <div class="dropdown">
                                <a class="langMD_bttn changeLocationBtn" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">
                                    <i class="las la-map-marker-alt"></i> <span id="location-text-mobile-header" style="display: inline-block; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: middle;">{{ __('website.your_location') }}</span>
                                </a>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap" style="margin-top: 2px;">
                                <div id="delivery-time-mobile-header" class="delivery-time-display" style="display: none; font-size: 11px; color: #666;">
                                    <i class="fa-solid fa-clock me-1" style="font-size: 8px;"></i>
                                    <span id="delivery-time-text-mobile-header"></span>
                                    <span id="delivery-price-mobile-header" style="margin-inline-start: 10px; display: none;">
                                        <i class="fa-solid fa-motorcycle me-1" style="font-size: 8px;"></i>
                                        <span id="delivery-price-text-mobile-header"></span>
                                    </span>
                                </div>
                                <div class="header-payment-icons d-flex align-items-center gap-1 desktop__none">
                                    <div class="payment-icon-wrapper visa-icon" title="{{ __('website.payment_visa_available') }}" style="font-size: 14px;">
                                        <i class="fa-brands fa-cc-visa"></i>
                                    </div>
                                    <div class="payment-icon-wrapper cash-icon disabled" title="{{ __('website.payment_cash_unavailable') }}" style="font-size: 14px;">
                                        <i class="fa-solid fa-money-bill-1-wave"></i>
                                        <span class="unavailable-mark" style="width: 10px; height: 10px; font-size: 8px; top: -3px; right: -3px;">x</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                            @if(LaravelLocalization::getCurrentLocale() != $localeCode)
                                <a href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                                   class="mob_link">
                                    <img src="{{ asset('website') }}/assets/img/{{ $localeCode == 'ar' ? 'kw' : 'en' }}.png" alt="" class="userLog_icon" loading="lazy" decoding="async">
                                    <span>{{ strtoupper($localeCode) }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                    <form action="{{ route('website.search') }}" class="search_form">
                        <div class="form_group">
                            <button class="transbrder_bttn abSearch_bttn">
                                <i class="las la-search searchSM_icon"></i>
                            </button>
                            <input type="search" name="keyword" class="search_input" placeholder="{{ __('website.search_for_product') }}">
                        </div>
                    </form>
                    <div class="navbar__collapse d__mob__none">
                        <ul class="my__navbar">
                            <li class="nav-item {{ Request::routeIs('website.home') || Request::is('/') ? 'active' : '' }}">
                                <a href="{{ route('website.home') }}" class="nav-link">{{ __('website.home') }}</a>
                            </li>
                            <li class="nav-item {{ Request::routeIs('website.categories') || Request::routeIs('website.products') ? 'active' : '' }}">
                                <a href="{{ route('website.categories') }}" class="nav-link">{{ __('website.categories') }}</a>
                            </li>
                            <li class="nav-item {{ Request::routeIs('website.menus') || Request::routeIs('website.menu') || Request::is('*/menus*') ? 'active' : '' }}">
                                <a href="{{ route('website.menus') }}" class="nav-link">{{ __('website.menus') }}</a>
                            </li>
                            <li class="nav-item {{ Request::routeIs('website.branches') || Request::is('*/branches*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('website.branches') }}">{{ __('website.branches') }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="buttons_wrapper gap-4 d__mob__none">
                        @auth('web')
                            <a href="{{ route('profile.index') }}" class="rounded_bttn"> <i class="las la-user"></i> </a>
                            <a href="{{ route('website.logout') }}" class="rounded_bttn text-dark">  <i class="fa-solid fa-right-from-bracket"></i> </a>
                        @else
                            <a href="{{ route('website.login') }}" class="rounded_bttn"> <i class="las la-user"></i> </a>
                        @endauth
                        <div class="d-flex flex-column align-items-center">
                        <button class="rounded_bttn changeLocationBtn" title="{{ __('website.change_location') }}">
<i class="las la-map-marker-alt"></i>
                        </button>
                            <div id="delivery-time-desktop" class="delivery-time-display" style="display: none; font-size: 11px; color: #666; margin-top: 3px; text-align: center;">
                                <div><i class="fa-solid fa-clock me-1"></i><span id="delivery-time-text-desktop"></span></div>
                                <div id="delivery-price-desktop" style="display: none;"><span id="delivery-price-text-desktop"></span></div>
                            </div>
                        </div>
                        <a href="#" class="rounded_bttn openCart_menu" style="position: relative;">
                            <i class="las la-shopping-bag"></i>
                            @if($cartCount > 0)
                                <span id="cartCount" class="cart_badge">{{ $cartCount }}</span>
                            @else
                                <span id="cartCount" class="cart_badge" style="display: none;">0</span>
                            @endif
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
@endunless

