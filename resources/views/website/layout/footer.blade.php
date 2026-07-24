<!--Start footer -->
@php
    // Optimize: Use cached setting model
    $settingModel = \App\Models\Setting::getSettingModel();
    $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');

    // Get socials settings and extract Instagram URL
    $socials = \Illuminate\Support\Facades\Cache::remember('website_socials', 3600, function () {
        $settings_socials = \App\Models\Setting::where('key', 'socials')->first();
        $socialsJson = $settings_socials?->value ?? '[]';
        return json_decode($socialsJson, true) ?? [];
    });

    // Get Instagram URL from socials
    $instagramUrl = '#';
    foreach ($socials as $social) {
        if (isset($social['name']) && strtolower($social['name']) === 'instagram') {
            $instagramUrl = $social['url'] ?? '#';
            break;
        }
        // Also check by icon name
        if (isset($social['icon']) && (stripos($social['icon'], 'instagram') !== false || stripos($social['icon'], 'insta') !== false)) {
            $instagramUrl = $social['url'] ?? '#';
            break;
        }
    }

    // Get contact phone for WhatsApp
    $contactPhone = \App\Models\Setting::getValue('contact_phone', null, '');
@endphp
@unless(Route::is('website.login', 'website.register', 'website.otp.verify', 'website.forget_pass', 'password.reset'))
<div class="footer d__mob__none" id="footer">
    <div class="container px-lg-0">
        <div class="footer_wrapper">
            <div class="row">
                <div class="col-3 col-lg-4">
                    <div class="footer_Column pb-3">
                        <a href="/" class="footer_logo">
                            <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::getValue('site_name', app()->getLocale(), 'Run2Diet') }}" loading="lazy" decoding="async">
                        </a>
                        <h5 class="downloadMB_title">{{ __('website.download_subscribe_now') }}</h5>
                        <div class="buttons_wrapper">
                            <a href="{{  \App\Models\Setting::getValue('android_url')  }}" class="download_bttn">
                                <img src="{{asset('website')}}/assets/img/g.png" alt="" class="app_thumb" loading="lazy" decoding="async">
                            </a>
                            <a href="{{  \App\Models\Setting::getValue('ios_url')  }}" class="download_bttn">
                                <img src="{{asset('website')}}/assets/img/a.png" alt="" class="app_thumb" loading="lazy" decoding="async">
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-3 col-lg-2">
                    <div class="footer_Column pb-4">
                        <h3 class="footer_title">{{ __('website.my_account') }}</h3>
                        <ul class="footer_list">
                            <li> <a href="{{ route('profile.index') }}" class="footer_link {{ Request::routeIs('profile.*') ? 'active_Mlink' : '' }}">{{ __('website.my_account') }}</a> </li>
                            <li> <a href="{{ route('profile.index') }}" class="footer_link {{ Request::routeIs('profile.*') && request()->get('tab') == 'track_orders' ? 'active_Mlink' : '' }}" onclick="openProfileTab('track_orders')">{{ __('website.track_order') }}</a> </li>
                        </ul>
                    </div>
                </div>
                <div class="col-3 col-lg-2">
                    <div class="footer_Column pb-4">
                        <h3 class="footer_title">{{ __('website.information') }}</h3>
                        <ul class="footer_list">
                            <li> <a href="{{ route('website.pages.show', 'about-us') }}" class="footer_link {{ Request::is('*/page/about-us') ? 'active_Mlink' : '' }}">{{ __('website.about_us') }}</a> </li>
                            <li> <a href="{{ route('website.pages.show', 'delivery-info') }}" class="footer_link {{ Request::is('*/page/delivery-info') ? 'active_Mlink' : '' }}">{{ __('website.delivery_info') }}</a> </li>
                            <li> <a href="{{ route('website.pages.show', 'privacy-policy') }}" class="footer_link {{ Request::is('*/page/privacy-policy') ? 'active_Mlink' : '' }}">{{ __('website.privacy_policy') }}</a> </li>
                            <li> <a href="{{ route('website.pages.show', 'terms-conditions') }}" class="footer_link {{ Request::is('*/page/terms-conditions') ? 'active_Mlink' : '' }}">{{ __('website.terms_and_conditions') }}</a> </li>
                        </ul>
                    </div>
                </div>
                @php
                    // Socials already loaded at top of file
                @endphp
                <div class="col-3 col-lg-4">
                    <div class="footer_Column pb-4">
                        <h3 class="footer_title">{{ __('website.contact') }}</h3>
                        <ul class="footer_list">
                            <li> <a href="#" class="footer_link {{ Request::is('*/page/contact-us') ? 'active_Mlink' : '' }}">{{ __('website.contact') }}</a> </li>
                        </ul>
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
    </div>
</div>
<div class="copyright-section">
    <div class="container px-lg-0">
        <p class="copyright-text">© {{ date('Y') }} Next Level. {{ __('website.all_rights_reserved') }}</p>
    </div>
</div>
@endunless

<a href="#" class="go-top" data-toggle="tooltip" title="" data-placement="left" data-original-title="{{ __('website.go_to_top') }}" >
    <i class="fa-solid fa-chevron-up"></i>
</a>

<div class="fixed_SocialN desktop__none">
    @if($contactPhone)
    @php
        // Clean phone number - remove any non-digit characters for wa.me format
        $cleanPhone = preg_replace('/[^0-9]/', '', $contactPhone);
    @endphp
    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" rel="noopener noreferrer" class="aside_SocICON">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
    @endif
    @if($instagramUrl && $instagramUrl !== '#')
    <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" class="aside_SocICON">
        <i class="fa-brands fa-instagram"></i>
    </a>
    @endif
</div>

<div class="fixBttom_Menu desktop__none">
    <ul class="fixBttom_Mlist">
        <li>
            <a href="{{ route('website.home') }}" class="fixBttom_Mlink {{ Request::is('/') ? 'active_Mlink' : '' }}">

                <i class="fa-solid fa-house"></i>
                <span> {{ __('website.home') }} </span>
            </a>
        </li>
        <li>
            <a href="{{ route('website.categories') }}" class="fixBttom_Mlink {{ Request::routeIs('website.categories') || Request::routeIs('website.products.show') ? 'active_Mlink' : '' }}">
                <i class="fa-solid fa-layer-group"></i>
                <span> {{ __('website.categories') }} </span>
            </a>
        </li>
        <li>
            <a href="{{ route('website.menus') }}" class="fixBttom_Mlink {{ Request::routeIs('website.menus') ? 'active_Mlink' : '' }}">
                <i class="fa-solid fa-utensils"></i>
                <span> {{ __('website.menus') }} </span>
            </a>
        </li>
        <li>
            <a href="{{ route('profile.index') }}" class="fixBttom_Mlink {{ Request::routeIs('profile.*') || Request::routeIs('website.wallet.*') ? 'active_Mlink' : '' }}">
                <i class="fa-solid fa-user"></i>
                <span> {{ __('website.profile') }} </span>
            </a>
        </li>
        <li>
            <a href="{{ route('cart.index') }}" class="fixBttom_Mlink {{ Request::routeIs('cart.*') || Request::routeIs('checkout*') || Request::routeIs('website.orders.*') ? 'active_Mlink' : '' }}" style="position: relative;">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>{{ __('website.cart') }}</span>
                @if($cartCount > 0)
                    <span id="cartCountMobile" class="cart_badge_mobile">{{ $cartCount }}</span>
                @else
                    <span id="cartCountMobile" class="cart_badge_mobile" style="display: none;">0</span>
                @endif
            </a>
        </li>
    </ul>
</div>


{{--location modal--}}
<!-- Modal -->

<!-- Modal -->
<div class="modal fade" id="locationModal" tabindex="-1" data-bs-backdrop="{{ session('user_location') ? 'true' : 'static' }}" data-bs-keyboard="{{ session('user_location') ? 'true' : 'false' }}">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg rounded-0 border-0">
{{--            @if(session('user_location'))--}}
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
{{--            @endif--}}
            <div class="modal-body p-4">
                @auth('web')
                <!-- Addresses List for Logged-in Users - Hidden -->
                <div class="mb-3" style="display: none;">
                    <h6 class="mb-3">{{ __('website.my_addresses') }}</h6>
                    <div id="header-addresses-list" class="list-group" style="max-height: 200px; overflow-y: auto;">
                        <!-- Addresses will be loaded here -->
                    </div>
                    <div id="header-no-addresses" class="alert alert-info" style="display: none;">
                        <p class="mb-2">{{ __('website.no_addresses_found') }}</p>
                        <a href="{{ route('profile.index') }}?tab=addresses" class="btn btn-sm btn-primary">
                            {{ __('website.add_address') }}
                        </a>
                    </div>
                </div>
                @endauth

                <!-- State and City Selection -->
                <div class="row mb-3">
                    <div class="col-12 mb-3">
                        <label for="header_state" class="form-label fw-bold">{{ __('website.state') }} <span class="text-danger">*</span></label>
                        <select class="form-control" id="header_state" name="state" required style="width: 100%;">
                            <option value="">{{ __('website.select_state') ?? 'Select State' }}</option>
                        </select>
                        <input type="hidden" id="header_state_name" name="state_name">
                    </div>
                    <div class="col-12 mb-3">
                        <label for="header_city" class="form-label fw-bold">{{ __('website.city') }} <span class="text-danger">*</span></label>
                        <select class="form-control select2-city" id="header_city" name="city" disabled required style="width: 100%;">
                            <option value="">{{ __('website.select_city') ?? 'Select City' }}</option>
                        </select>
                        <input type="hidden" id="header_city_name" name="city_name">
                    </div>
                </div>

                <!-- Pickup Branch Selection (only for pickup) -->
                @php
                    $menuType = session('menu_type', 'delivery');
                @endphp
                <div class="row mb-3" id="header-pickup-branch-row" style="display: {{ $menuType === 'pickup' ? 'block' : 'none' }};">
                    <div class="col-12 mb-3">
                        <label for="header_pickup_branch" class="form-label fw-bold">{{ __('website.select_pickup_branch') ?? 'Select Pickup Branch' }} <span class="text-danger">*</span></label>
                        <select class="form-control" id="header_pickup_branch" name="pickup_branch" disabled style="width: 100%;">
                            <option value="">{{ __('website.choose_branch') ?? 'Choose Branch' }}</option>
                        </select>
                        <div id="header-branches-loading" style="display:none;" class="mt-2">
                            <small class="text-muted">{{ __('website.loading') ?? 'Loading...' }}</small>
                        </div>
                        <div id="header-no-branches-message" style="display:none;" class="mt-2">
                            <small class="text-danger">{{ __('website.no_branches_available') ?? 'No branches available for this city' }}</small>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs for location data -->
                <input type="hidden" id="selected_latitude" value="">
                <input type="hidden" id="selected_longitude" value="">
                @php
                    // Optimize: Cache country ID
                    $defaultCountryId = Cache::remember('default_country_id', 3600, function () {
                        return \App\Models\Location::where('type', 'country')->where('active', true)->orderBy('id')->value('id');
                    });
                @endphp
                <input type="hidden" id="header_country_id" name="country_id" value="{{ $defaultCountryId ?? '' }}">
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button id="saveLocation" class="main_bttn mid_bttn login_bttn hvr-sweep-to-right w-100" disabled>
                    {{ __('website.save') }} </button>
            </div>
        </div>
    </div>
</div>

{{--end location modal--}}

<script src="{{ asset('website') }}/assets/js/jquery-3.2.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('website') }}/assets/js/jquery.fancybox.min.js"></script>
<script src="{{ asset('website') }}/assets/js/slick.min.js"></script>
<script src="{{ asset('website') }}/assets/js/jquery.nice-select.js"></script>
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script src="{{ asset('website') }}/assets/js/popper.min.js"></script>
<script src="{{ asset('website') }}/assets/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('website') }}/assets/js/all.min.js"></script>
<!-- Select2 for city search -->
<link rel="stylesheet" href="{{ asset('dashboard') }}/assets/vendor/libs/select2/select2.css" />
<script src="{{ asset('dashboard') }}/assets/vendor/libs/select2/select2.js"></script>
<script src="{{ asset('website') }}/assets/js/main.js?ver=7.2"></script>
<script src="{{ asset('website') }}/assets/js/wow.min.js"></script>

<script>
    function openProfileTab(tabName) {
        sessionStorage.setItem('openProfileTab', tabName);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const tabToOpen = sessionStorage.getItem('openProfileTab');
        if (tabToOpen && window.location.pathname.includes('/profile')) {
            sessionStorage.removeItem('openProfileTab');

            setTimeout(function() {
                if (typeof loadTabContent === 'function') {
                    loadTabContent(tabToOpen);

                    $('.tab_link').removeClass('active_catlink');
                    $(`[data-tab="${tabToOpen}"]`).addClass('active_catlink');
                } else {
                    const tabElement = document.querySelector(`[data-tab="${tabToOpen}"]`);
                    if (tabElement) {
                        tabElement.click();
                    }
                }
            }, 1000);
        }
    });
</script>

<script>
    new WOW().init();
</script>

@section('website-footer')

@show
<script>
    const AppSwal = {
        toast: (icon, message, title = '') => {
            return Swal.fire({
                icon: icon,
                title: title || message,
                text: title ? message : '',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        },
        success: (message, title = '') => AppSwal.toast('success', message, title),
        error: (message, title = '{{ __("website.error") }}') => AppSwal.toast('error', message, title),
        warning: (message, title = '{{ __("website.warning") }}') => AppSwal.toast('warning', message, title),
        info: (message, title = '{{ __("website.info") }}') => AppSwal.toast('info', message, title),
        alert: (message, title = '') => {
            return Swal.fire({
                icon: 'info',
                title: title || message,
                text: title ? message : '',
                confirmButtonText: '{{ __("website.ok") }}'
            });
        },

        confirm: (options) => {
            return Swal.fire({
                icon: options.icon || 'warning',
                title: options.title || '{{ __("website.are_you_sure") }}',
                text: options.text || '',
                showCancelButton: true,
                confirmButtonText: options.confirmButtonText || '{{ __("website.yes") }}',
                cancelButtonText: options.cancelButtonText || '{{ __("website.cancel") }}',
                reverseButtons: true
            });
        }
    };

    // Keep SwalConfig as alias for backward compatibility
    const SwalConfig = AppSwal;

    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
        AppSwal.success("{{ session('success') }}");
        @endif

        @if(session('error'))
        AppSwal.error("{{ session('error') }}");
        @endif
    });

</script>

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let errorList = '';
            @foreach ($errors->all() as $error)
                errorList += '• {{ e($error) }}\n';
            @endforeach

            AppSwal.error(errorList, '{{ __("website.error") }}');
        });
    </script>
@endif

<script>
    // show password
    $(".toggle-password").click(function(){
        let input = $("#form_password");

        if (input.attr("type") === "password") {
            input.attr("type", "text");
            $(".passToggle_icon.fa-eye-slash").hide();
            $(".passToggle_icon.fa-eye").show();
        } else {
            input.attr("type", "password");
            $(".passToggle_icon.fa-eye-slash").show();
            $(".passToggle_icon.fa-eye").hide();
        }
    });
</script>
<script>
    // Geolocation auto-request disabled to prevent browser location permission popup
    // Location will only be requested when user explicitly clicks a "Locate Me" button
    // if (navigator.geolocation) {
    //     navigator.geolocation.getCurrentPosition(function(position) {
    //         fetch("{{ url('/save-location') }}?lat=" + position.coords.latitude + "&long=" + position.coords.longitude);
    //     });
    // }
</script>


<script>
    let selectedHeaderAddress = null;
    let selectedStateId = null;
    let selectedCityId = null;

    // Load states for header location modal
    function loadHeaderStates() {
        const stateSelect = document.getElementById('header_state');
        const citySelect = document.getElementById('header_city');
        const countryId = document.getElementById('header_country_id').value;

        stateSelect.innerHTML = '<option value="">{{ __("website.select_state") ?? "Select State" }}</option>';
        stateSelect.disabled = true;
        citySelect.innerHTML = '<option value="">{{ __("website.select_city") ?? "Select City" }}</option>';
        citySelect.disabled = true;

        fetch('{{ route("website.locations.states") }}?country_id=' + countryId, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(function(state) {
                    const option = document.createElement('option');
                    option.value = parseInt(state.id); // Ensure it's an integer
                    option.textContent = state.name;
                    stateSelect.appendChild(option);
                });
                stateSelect.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error loading states:', error);
        });
    }

    // Load cities for header location modal
    function loadHeaderCities(stateId) {
        const citySelect = document.getElementById('header_city');

        // Destroy Select2 if it exists
        if ($(citySelect).hasClass('select2-hidden-accessible')) {
            $(citySelect).select2('destroy');
        }

        citySelect.innerHTML = '<option value="">{{ __("website.select_city") ?? "Select City" }}</option>';
        citySelect.disabled = true;

        if (!stateId) {
            // Reinitialize Select2 even when disabled - attach dropdown to modal
            $(citySelect).select2({
                placeholder: '{{ __("website.select_city") ?? "Select City" }}',
                allowClear: false,
                disabled: true,
                minimumResultsForSearch: 0,
                width: '100%',
                dropdownParent: $('#locationModal')
            });
            return;
        }

        fetch('{{ route("website.locations.cities") }}?state_id=' + stateId, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(function(city) {
                    const option = document.createElement('option');
                    option.value = parseInt(city.id); // Ensure it's an integer
                    option.textContent = city.name;
                    option.setAttribute('data-delivery-time', city.delivery_time || '');
                    citySelect.appendChild(option);
                });
                citySelect.disabled = false;

                // Initialize Select2 with search - attach dropdown to modal to fix z-index
                $(citySelect).select2({
                    placeholder: '{{ __("website.select_city") ?? "Select City" }}',
                    allowClear: false,
                    minimumResultsForSearch: 0, // Always show search box
                    width: '100%',
                    dropdownParent: $('#locationModal'),
                    language: {
                        noResults: function() {
                            return '{{ __("website.no_results_found") ?? "No results found" }}';
                        },
                        searching: function() {
                            return '{{ __("website.searching") ?? "Searching..." }}';
                        }
                    }
                });
            } else {
                    // Reinitialize Select2 even if no cities - attach dropdown to modal
                $(citySelect).select2({
                    placeholder: '{{ __("website.select_city") ?? "Select City" }}',
                    allowClear: false,
                    disabled: true,
                    minimumResultsForSearch: 0,
                    width: '100%',
                    dropdownParent: $('#locationModal')
                });
            }
        })
        .catch(error => {
            console.error('Error loading cities:', error);
            // Reinitialize Select2 on error - attach dropdown to modal
            $(citySelect).select2({
                placeholder: '{{ __("website.select_city") ?? "Select City" }}',
                allowClear: false,
                disabled: true,
                minimumResultsForSearch: 0,
                width: '100%',
                dropdownParent: $('#locationModal')
            });
        });
    }

    // Update delivery time in header
    function updateHeaderDeliveryTime(cityId) {
        if (!cityId) {
            hideHeaderDeliveryTime();
            return;
        }

        // Hide delivery time if pickup is selected
        const menuType = '{{ session("menu_type", "delivery") }}';
        if (menuType === 'pickup') {
            hideHeaderDeliveryTime();
            return;
        }

        fetch('{{ route("website.locations.delivery-time") }}?city_id=' + cityId, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.delivery_time) {
                const deliveryTimeText = data.delivery_time + ' {{ __("admin.hours") }}';

                // Show delivery time in desktop header
                const desktopDeliveryTime = document.getElementById('delivery-time-desktop');
                const desktopDeliveryTimeText = document.getElementById('delivery-time-text-desktop');
                const desktopDeliveryPrice = document.getElementById('delivery-price-desktop');
                const desktopDeliveryPriceText = document.getElementById('delivery-price-text-desktop');

                if (desktopDeliveryTime && desktopDeliveryTimeText) {
                    desktopDeliveryTime.style.display = 'block';
                    desktopDeliveryTimeText.textContent = deliveryTimeText;

                    // Add price if available - with icon for desktop too
                    if (data.delivery_cost && desktopDeliveryPrice && desktopDeliveryPriceText) {
                        desktopDeliveryPrice.style.display = 'block';
                        desktopDeliveryPriceText.innerHTML = '<i class="fa-solid fa-motorcycle me-1"></i> ' + data.delivery_cost + ' ' + (data.currency || '{{ \App\Models\Currency::getCurrentCurrencySign() }}');
                    }
                }

                // Show delivery time in mobile header
                const mobileDeliveryTime = document.getElementById('delivery-time-mobile');
                const mobileDeliveryTimeText = document.getElementById('delivery-time-text-mobile');

                if (mobileDeliveryTime && mobileDeliveryTimeText) {
                    mobileDeliveryTime.style.display = 'block';
                    // Check if price element exists inside or append it
                    let priceHtml = '';
                    if (data.delivery_cost) {
                        priceHtml = ' <span style="margin-inline-start: 10px;"><i class="fa-solid fa-motorcycle me-1"></i> ' + data.delivery_cost + ' ' + (data.currency || '{{ \App\Models\Currency::getCurrentCurrencySign() }}') + '</span>';
                    }
                    mobileDeliveryTimeText.innerHTML = deliveryTimeText + priceHtml;
                }

                // Show delivery time in mobile header (alternative)
                const mobileHeaderDeliveryTime = document.getElementById('delivery-time-mobile-header');
                const mobileHeaderDeliveryTimeText = document.getElementById('delivery-time-text-mobile-header');
                const mobileHeaderDeliveryPrice = document.getElementById('delivery-price-mobile-header');
                const mobileHeaderDeliveryPriceText = document.getElementById('delivery-price-text-mobile-header');

                if (mobileHeaderDeliveryTime && mobileHeaderDeliveryTimeText) {
                    mobileHeaderDeliveryTime.style.display = 'block';
                    mobileHeaderDeliveryTimeText.textContent = deliveryTimeText;

                    // Add price if available - with icon for mobile
                    if (data.delivery_cost && mobileHeaderDeliveryPrice && mobileHeaderDeliveryPriceText) {
                        mobileHeaderDeliveryPrice.style.display = 'inline';
                        mobileHeaderDeliveryPriceText.textContent = data.delivery_cost + ' ' + (data.currency || '{{ \App\Models\Currency::getCurrentCurrencySign() }}');
                    }
                }
            } else {
                hideHeaderDeliveryTime();
            }
        })
        .catch(error => {
            console.error('Error loading delivery time:', error);
            hideHeaderDeliveryTime();
        });
    }

    // Hide delivery time in header
    function hideHeaderDeliveryTime() {
        const desktopDeliveryTime = document.getElementById('delivery-time-desktop');
        const mobileDeliveryTime = document.getElementById('delivery-time-mobile');
        const mobileHeaderDeliveryTime = document.getElementById('delivery-time-mobile-header');

        if (desktopDeliveryTime) desktopDeliveryTime.style.display = 'none';
        if (mobileDeliveryTime) mobileDeliveryTime.style.display = 'none';
        if (mobileHeaderDeliveryTime) mobileHeaderDeliveryTime.style.display = 'none';
    }

    @auth('web')
    // Load addresses for header location modal
    function loadHeaderAddresses() {
        fetch('{{ route("addresses.index") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.addresses) {
                const addressesList = document.getElementById('header-addresses-list');
                const noAddresses = document.getElementById('header-no-addresses');

                if (data.addresses.length === 0) {
                    addressesList.innerHTML = '';
                    noAddresses.style.display = 'block';
                } else {
                    noAddresses.style.display = 'none';
                    let html = '';

                    data.addresses.forEach(function(address) {
                        const isMain = address.is_main ? '<span class="badge bg-primary ms-2">{{ __("website.main_address") }}</span>' : '';
                        const isSelected = selectedHeaderAddress && selectedHeaderAddress.id == address.id ? 'selected' : '';

                        // Get state and city IDs from address
                        const stateId = address.state_id || '';
                        const cityId = address.city_id || '';
                        const stateName = address.state || '';
                        const cityName = address.city || '';

                        html += `
                            <div class="list-group-item header-address-item ${isSelected}"
                                 data-address-id="${address.id}"
                                 data-state-id="${stateId}"
                                 data-city-id="${cityId}"
                                 data-state="${stateName}"
                                 data-city="${cityName}"
                                 data-address-text="${address.full_address || address.address || ''}"
                                 style="cursor: pointer; transition: all 0.3s ease;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            ${address.title || '{{ __("website.address") }} #' + address.id}
                                            ${isMain}
                                        </h6>
                                        <p class="mb-1 small text-muted">${address.full_address || address.address || ''}</p>
                                    </div>
                                    ${!address.is_main ? `
                                        <button type="button" class="btn btn-sm btn-outline-primary set-main-header-address-btn"
                                                data-address-id="${address.id}"
                                                onclick="event.stopPropagation(); setMainHeaderAddress(${address.id});">
                                            <i class="fa fa-star"></i>
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                        `;
                    });

                    addressesList.innerHTML = html;

                    // Add click handlers
                    document.querySelectorAll('.header-address-item').forEach(function(item) {
                        item.addEventListener('click', function() {
                            // Remove selected class from all items
                            document.querySelectorAll('.header-address-item').forEach(function(i) {
                                i.classList.remove('selected');
                            });
                            // Add selected class to clicked item
                            this.classList.add('selected');

                            const addressId = this.getAttribute('data-address-id');
                            const state = this.getAttribute('data-state');
                            const city = this.getAttribute('data-city');
                            const stateId = this.getAttribute('data-state-id');
                            const cityId = this.getAttribute('data-city-id');
                            const addressText = this.getAttribute('data-address-text');

                            selectedHeaderAddress = {
                                id: addressId,
                                state: state,
                                city: city,
                                state_id: stateId,
                                city_id: cityId,
                                address: addressText
                            };

                            // Update state and city dropdowns
                            if (stateId && cityId) {
                                const stateSelect = document.getElementById('header_state');
                                const citySelect = document.getElementById('header_city');

                                if (stateSelect && stateSelect.value !== stateId) {
                                    stateSelect.value = stateId;
                                    stateSelect.dispatchEvent(new Event('change'));

                                    setTimeout(function() {
                                        if (citySelect && citySelect.value !== cityId) {
                                            citySelect.value = cityId;
                                            // Update Select2 if it exists
                                            if ($(citySelect).hasClass('select2-hidden-accessible')) {
                                                $(citySelect).trigger('change.select2');
                                            } else {
                                                citySelect.dispatchEvent(new Event('change'));
                                            }
                                        }
                                    }, 500);
                                }
                            }
                        });
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error loading addresses:', error);
        });
    }

    // Set main address function
    function setMainHeaderAddress(addressId) {
        fetch('{{ route("addresses.set.main", ":id") }}'.replace(':id', addressId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                AppSwal.success(data.message, '{{ __("website.success") }}');
                loadHeaderAddresses();
            }
        })
        .catch(error => {
            console.error('Error setting main address:', error);
        });
    }

    // Make function globally accessible
    window.setMainHeaderAddress = setMainHeaderAddress;
    @endauth

    // Function to update location text in header
    function updateLocationText(stateName, cityName) {
        // Get default country name
        const countryName = '{{ \App\Models\Location::where("type", "country")->where("active", true)->orderBy("id")->first()?->getTranslation("name", app()->getLocale()) ?? "" }}';

        // Build location string: country, state, city
        const locationText = [countryName, stateName, cityName].filter(Boolean).join(', ');

        // Update mobile sidebar location text
        const mobileSidebarText = document.getElementById('location-text-mobile-sidebar');
        if (mobileSidebarText) {
            mobileSidebarText.textContent = locationText || '{{ __("website.your_location") }}';
        }

        // Update mobile header location text (limit to 100 characters)
        const mobileHeaderText = document.getElementById('location-text-mobile-header');
        if (mobileHeaderText) {
            let displayText = locationText || '{{ __("website.your_location") }}';
            // No truncation here, relying on CSS ellipsis
            mobileHeaderText.textContent = displayText;
            mobileHeaderText.title = displayText; // Tooltip for full text
        }
    }

    // Update location text and delivery time on page load if location is saved
    function updateLocationTextOnLoad() {
        const userLocation = @json(session('user_location'));
        if (userLocation && userLocation.state && userLocation.city) {
            const countryName = userLocation.country_name || '{{ \App\Models\Location::where("type", "country")->where("active", true)->orderBy("id")->first()?->getTranslation("name", app()->getLocale()) ?? "" }}';
            const stateName = userLocation.state || '';
            const cityName = userLocation.city || '';
            updateLocationText(stateName, cityName);

            // Update delivery time if city_id is available (only for delivery, not pickup)
            const menuType = '{{ session("menu_type", "delivery") }}';
            if (menuType !== 'pickup') {
                if (userLocation.city_id) {
                    updateHeaderDeliveryTime(userLocation.city_id);
                } else if (userLocation.delivery_time) {
                    // If delivery_time is in session, display it directly
                    const deliveryTimeText = userLocation.delivery_time + ' {{ __("admin.hours") }}';

                    // Show delivery time in desktop header
                    const desktopDeliveryTime = document.getElementById('delivery-time-desktop');
                    const desktopDeliveryTimeText = document.getElementById('delivery-time-text-desktop');
                    const desktopDeliveryPrice = document.getElementById('delivery-price-desktop');
                    const desktopDeliveryPriceText = document.getElementById('delivery-price-text-desktop');

                    if (desktopDeliveryTime && desktopDeliveryTimeText) {
                        desktopDeliveryTime.style.display = 'block';
                        desktopDeliveryTimeText.textContent = deliveryTimeText;

                        // Add price if available
                        if (userLocation.delivery_cost && desktopDeliveryPrice && desktopDeliveryPriceText) {
                            desktopDeliveryPrice.style.display = 'block';
                            desktopDeliveryPriceText.innerHTML = '<i class="fa-solid fa-motorcycle me-1"></i> ' + userLocation.delivery_cost + ' {{ \App\Models\Currency::getCurrentCurrencySign() }}';
                        }
                    }

                    // Show delivery time in mobile header
                    const mobileDeliveryTime = document.getElementById('delivery-time-mobile');
                    const mobileDeliveryTimeText = document.getElementById('delivery-time-text-mobile');
                    if (mobileDeliveryTime && mobileDeliveryTimeText) {
                        mobileDeliveryTime.style.display = 'block';
                        // Check if price element exists inside or append it
                        let priceHtml = '';
                        if (userLocation.delivery_cost) {
                            priceHtml = ' <span style="margin-inline-start: 10px;"><i class="fa-solid fa-motorcycle me-1"></i> ' + userLocation.delivery_cost + ' {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>';
                        }
                        mobileDeliveryTimeText.innerHTML = deliveryTimeText + priceHtml;
                    }

                    // Show delivery time in mobile header (alternative)
                    const mobileHeaderDeliveryTime = document.getElementById('delivery-time-mobile-header');
                    const mobileHeaderDeliveryTimeText = document.getElementById('delivery-time-text-mobile-header');
                    const mobileHeaderDeliveryPrice = document.getElementById('delivery-price-mobile-header');
                    const mobileHeaderDeliveryPriceText = document.getElementById('delivery-price-text-mobile-header');

                    if (mobileHeaderDeliveryTime && mobileHeaderDeliveryTimeText) {
                        mobileHeaderDeliveryTime.style.display = 'block';
                        mobileHeaderDeliveryTimeText.textContent = deliveryTimeText;

                        // Add price if available
                        if (userLocation.delivery_cost && mobileHeaderDeliveryPrice && mobileHeaderDeliveryPriceText) {
                            mobileHeaderDeliveryPrice.style.display = 'inline';
                            mobileHeaderDeliveryPriceText.textContent = userLocation.delivery_cost + ' {{ \App\Models\Currency::getCurrentCurrencySign() }}';
                        }
                    }
                }
            } else {
                // Hide delivery time for pickup
                hideHeaderDeliveryTime();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Update location text on page load
        updateLocationTextOnLoad();

        let modalEl = document.getElementById('locationModal');
        let modal = null;
        let saveBtn = document.getElementById('saveLocation');
        let userLocation = @json(session('user_location'));

        // Initialize modal if element exists
        if (modalEl) {
            try {
                modal = new bootstrap.Modal(modalEl, {
                    backdrop: modalEl.getAttribute('data-bs-backdrop') === 'true' ? true : 'static',
                    keyboard: modalEl.getAttribute('data-bs-keyboard') === 'true'
                });
            } catch (error) {
                console.error('Error initializing location modal:', error);
            }
        }

        // Function to open location modal
        function openLocationModal() {
            if (!modalEl) {
                console.error('Location modal element not found');
                return;
            }

            if (!modal) {
                try {
                    modal = new bootstrap.Modal(modalEl, {
                        backdrop: modalEl.getAttribute('data-bs-backdrop') === 'true' ? true : 'static',
                        keyboard: modalEl.getAttribute('data-bs-keyboard') === 'true'
                    });
                } catch (error) {
                    console.error('Error creating modal instance:', error);
                    return;
                }
            }

            try {
                modal.show();
                // Load states when modal is shown
                setTimeout(function() {
                    if (typeof loadHeaderStates === 'function') {
                        loadHeaderStates();
                    }
                    @if(auth('web')->check())
                    if (typeof loadHeaderAddresses === 'function') {
                        loadHeaderAddresses();
                    }
                    @endif
                    // Show/hide pickup branch row based on menu type
                    const menuType = '{{ session("menu_type", "delivery") }}';
                    const pickupBranchRow = document.getElementById('header-pickup-branch-row');
                    if (pickupBranchRow) {
                        pickupBranchRow.style.display = menuType === 'pickup' ? 'block' : 'none';
                    }
                }, 300);
            } catch (error) {
                console.error('Error showing modal:', error);
            }
        }

        // Use event delegation to handle dynamically loaded buttons
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.changeLocationBtn, #changeLocationBtnMobile');
            if (btn) {
                e.preventDefault();
                e.stopPropagation();
                openLocationModal();
            }
        });

        // Handle state change
        const headerStateSelect = document.getElementById('header_state');
        if (headerStateSelect) {
            headerStateSelect.addEventListener('change', function() {
                const stateId = this.value;
                const stateName = this.options[this.selectedIndex].text;
                selectedStateId = stateId;
                document.getElementById('header_state_name').value = stateName;

                if (stateId) {
                    loadHeaderCities(stateId);
                } else {
                    const citySelect = document.getElementById('header_city');
                    // Destroy Select2 if it exists
                    if ($(citySelect).hasClass('select2-hidden-accessible')) {
                        $(citySelect).select2('destroy');
                    }
                    citySelect.innerHTML = '<option value="">{{ __("website.select_city") ?? "Select City" }}</option>';
                    citySelect.disabled = true;
                    // Reinitialize Select2 - attach dropdown to modal
                    $(citySelect).select2({
                        placeholder: '{{ __("website.select_city") ?? "Select City" }}',
                        allowClear: false,
                        disabled: true,
                        minimumResultsForSearch: 0,
                        width: '100%',
                        dropdownParent: $('#locationModal')
                    });
                    hideHeaderDeliveryTime();
                }
            });
        }

        // Handle city change (works with both regular select and Select2)
        const headerCitySelect = document.getElementById('header_city');
        if (headerCitySelect) {
            // Use jQuery for Select2 change event
            $(headerCitySelect).on('select2:select', function (e) {
                const cityId = e.params.data.id;
                const cityName = e.params.data.text;
                selectedCityId = cityId;
                document.getElementById('header_city_name').value = cityName;

                if (cityId) {
                    updateHeaderDeliveryTime(cityId);
                    // Load branches if pickup is selected
                    loadHeaderBranches(cityId);
                    document.getElementById('saveLocation').disabled = false;
                } else {
                    hideHeaderDeliveryTime();
                    // Clear branches if city is cleared
                    clearHeaderBranches();
                    document.getElementById('saveLocation').disabled = true;
                }
            });

            // Also handle regular change event as fallback
            headerCitySelect.addEventListener('change', function() {
                const cityId = this.value;
                const cityName = this.options[this.selectedIndex] ? this.options[this.selectedIndex].text : '';
                selectedCityId = cityId;
                document.getElementById('header_city_name').value = cityName;

                if (cityId) {
                    updateHeaderDeliveryTime(cityId);
                    // Load branches if pickup is selected
                    loadHeaderBranches(cityId);
                    document.getElementById('saveLocation').disabled = false;
                } else {
                    hideHeaderDeliveryTime();
                    // Clear branches if city is cleared
                    clearHeaderBranches();
                    document.getElementById('saveLocation').disabled = true;
                }
            });
        }

        // Load branches for pickup
        function loadHeaderBranches(cityId) {
            const menuType = '{{ session("menu_type", "delivery") }}';
            const pickupBranchRow = document.getElementById('header-pickup-branch-row');
            const branchSelect = document.getElementById('header_pickup_branch');
            const loadingDiv = document.getElementById('header-branches-loading');
            const noBranchesDiv = document.getElementById('header-no-branches-message');

            // Only load branches if pickup is selected
            if (menuType !== 'pickup') {
                if (pickupBranchRow) pickupBranchRow.style.display = 'none';
                return;
            }

            // Show branch selection row
            if (pickupBranchRow) pickupBranchRow.style.display = 'block';

            if (!cityId || !branchSelect) {
                clearHeaderBranches();
                return;
            }

            // Show loading
            if (loadingDiv) loadingDiv.style.display = 'block';
            if (noBranchesDiv) noBranchesDiv.style.display = 'none';
            if (branchSelect) {
                branchSelect.innerHTML = '<option value="">{{ __("website.choose_branch") ?? "Choose Branch" }}</option>';
                branchSelect.disabled = true;
            }

            // Load branches by city
            fetch('{{ route("website.branches.by-city") }}?city_id=' + cityId, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (loadingDiv) loadingDiv.style.display = 'none';

                if (data.status && data.branches && data.branches.length > 0) {
                    // Clear and populate branch select
                    if (branchSelect) {
                        branchSelect.innerHTML = '<option value="">{{ __("website.choose_branch") ?? "Choose Branch" }}</option>';

                        data.branches.forEach(function(branch) {
                            const option = document.createElement('option');
                            option.value = branch.id;
                            option.textContent = branch.name;
                            branchSelect.appendChild(option);
                        });

                        // Check if there's a saved branch
                        const savedBranchId = '{{ session("pickup_branch_id") }}';
                        if (savedBranchId) {
                            branchSelect.value = savedBranchId;
                        }

                        branchSelect.disabled = false;
                    }
                    if (noBranchesDiv) noBranchesDiv.style.display = 'none';
                } else {
                    if (branchSelect) {
                        branchSelect.innerHTML = '<option value="">{{ __("website.choose_branch") ?? "Choose Branch" }}</option>';
                        branchSelect.disabled = true;
                    }
                    if (noBranchesDiv) noBranchesDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error loading branches:', error);
                if (loadingDiv) loadingDiv.style.display = 'none';
                if (branchSelect) {
                    branchSelect.innerHTML = '<option value="">{{ __("website.choose_branch") ?? "Choose Branch" }}</option>';
                    branchSelect.disabled = true;
                }
                if (noBranchesDiv) noBranchesDiv.style.display = 'block';
            });
        }

        // Clear branches
        function clearHeaderBranches() {
            const branchSelect = document.getElementById('header_pickup_branch');
            const loadingDiv = document.getElementById('header-branches-loading');
            const noBranchesDiv = document.getElementById('header-no-branches-message');

            if (branchSelect) {
                branchSelect.innerHTML = '<option value="">{{ __("website.choose_branch") ?? "Choose Branch" }}</option>';
                branchSelect.disabled = true;
            }
            if (loadingDiv) loadingDiv.style.display = 'none';
            if (noBranchesDiv) noBranchesDiv.style.display = 'none';
        }

        modalEl.addEventListener('hidden.bs.modal', function () {
            if (!userLocation) {
                localStorage.setItem('location_modal_closed', '1');
            }
            fetch('{{ route("location.modal.closed") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
        });

        let modalClosed = @json(session('location_modal_closed'));
        if (!userLocation && !modalClosed) {
            modal.show();
            setTimeout(function() {
                loadHeaderStates();
            }, 300);
        }

        saveBtn.addEventListener('click', function() {
            const stateId = document.getElementById('header_state').value;
            const cityId = document.getElementById('header_city').value;
            const menuType = '{{ session("menu_type", "delivery") }}';

            if (!stateId || !cityId) {
                AppSwal.warning('{{ __("website.please_select_state_and_city") ?? "Please select state and city" }}', '{{ __("website.error") }}');
                return;
            }

            // Validate pickup branch selection if pickup is selected
            if (menuType === 'pickup') {
                const branchSelect = document.getElementById('header_pickup_branch');
                const branchId = branchSelect ? branchSelect.value : null;

                if (!branchId) {
                    AppSwal.warning('{{ __("website.please_select_pickup_branch") ?? "Please select pickup branch" }}', '{{ __("website.error") }}');
                    return;
                }
            }

            // If an address was selected, set it as main address
            @auth('web')
            if (selectedHeaderAddress && selectedHeaderAddress.id) {
                saveLocationAndSetMainAddress();
            } else {
                // Just save location to session if no address selected
                saveLocationToSession();
            }
            @else
            // For guests, just save location to session
            saveLocationToSession();
            @endauth
        });

        @auth('web')
        function saveLocationAndSetMainAddress() {
            // First set the address as main
            fetch('{{ route("addresses.set.main", ":id") }}'.replace(':id', selectedHeaderAddress.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Then save location to session
                    saveLocationToSession();
                } else {
                    AppSwal.error(data.message || '{{ __("website.failed_to_set_main_address") }}', '{{ __("website.error") }}');
                }
            })
            .catch(error => {
                console.error('Error setting main address:', error);
                AppSwal.error('{{ __("website.something_went_wrong") }}', '{{ __("website.error") }}');
            });
        }
        @endauth

        function saveLocationToSession() {
            const stateSelect = document.getElementById('header_state');
            const citySelect = document.getElementById('header_city');

            // Check if selects exist and have values
            if (!stateSelect || !citySelect || !stateSelect.value || !citySelect.value) {
                AppSwal.error('{{ __("website.please_select_state_and_city") ?? "Please select state and city" }}', '{{ __("website.error") }}');
                return;
            }

            // Get values and ensure they're integers
            const stateIdValue = stateSelect.value.trim();
            const cityIdValue = citySelect.value.trim();

            // Validate they're numeric
            if (!/^\d+$/.test(stateIdValue) || !/^\d+$/.test(cityIdValue)) {
                console.error('Invalid ID format:', { stateIdValue, cityIdValue });
                AppSwal.error('{{ __("website.invalid_location_data") ?? "Invalid location data. Please try again." }}', '{{ __("website.error") }}');
                return;
            }

            const stateId = parseInt(stateIdValue, 10);
            const cityId = parseInt(cityIdValue, 10);
            const stateName = stateSelect.options[stateSelect.selectedIndex] ? stateSelect.options[stateSelect.selectedIndex].text : '';
            const cityName = citySelect.options[citySelect.selectedIndex] ? citySelect.options[citySelect.selectedIndex].text : '';

            // Validate that IDs are valid integers
            if (isNaN(stateId) || isNaN(cityId) || stateId <= 0 || cityId <= 0) {
                console.error('Invalid IDs:', { stateId, cityId });
                AppSwal.error('{{ __("website.please_select_state_and_city") ?? "Please select state and city" }}', '{{ __("website.error") }}');
                return;
            }

            // Double-check values before sending
            console.log('Sending location data:', {
                state_id: stateId,
                city_id: cityId,
                state: stateName,
                city: cityName
            });

            // Get pickup branch if pickup is selected
            const menuType = '{{ session("menu_type", "delivery") }}';
            const branchSelect = document.getElementById('header_pickup_branch');
            const branchId = (menuType === 'pickup' && branchSelect) ? branchSelect.value : null;

            fetch('{{ route("website.save.location") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    state_id: stateId,
                    city_id: cityId,
                    state: stateName,
                    city: cityName,
                    pickup_branch_id: branchId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    @if(auth('web')->check())
                    if (selectedHeaderAddress && selectedHeaderAddress.id) {
                        AppSwal.success('{{ __("website.location_saved_and_address_set_as_main") ?? "Location saved and address set as main" }}', '{{ __("website.success") }}');
                    } else {
                        AppSwal.success('{{ __("website.location_saved_successfully") ?? "Location saved successfully" }}', '{{ __("website.success") }}');
                    }
                    @else
                    AppSwal.success('{{ __("website.location_saved_successfully") ?? "Location saved successfully" }}', '{{ __("website.success") }}');
                    @endif

                    // Update location text immediately
                    const stateSelect = document.getElementById('header_state');
                    const citySelect = document.getElementById('header_city');
                    const currentStateName = stateSelect ? stateSelect.options[stateSelect.selectedIndex].text : stateName;
                    const currentCityName = citySelect ? citySelect.options[citySelect.selectedIndex].text : cityName;
                    updateLocationText(currentStateName, currentCityName);

                    // Update home page branch select if pickup is selected (before reload)
                    const menuType = '{{ session("menu_type", "delivery") }}';
                    const branchSelect = document.getElementById('header_pickup_branch');
                    const branchId = (menuType === 'pickup' && branchSelect) ? branchSelect.value : null;
                    if (menuType === 'pickup' && branchId) {
                        // Trigger event to update home page branch select
                        window.dispatchEvent(new CustomEvent('pickupBranchUpdated', {
                            detail: { branchId: branchId }
                        }));
                    }

                    setTimeout(function() {
                        if (modal) {
                            modal.hide();
                        }
                        if (modalEl) {
                            modalEl.setAttribute('data-bs-backdrop', 'true');
                            modalEl.setAttribute('data-bs-keyboard', 'true');
                        }
                        location.reload();
                    }, 2000);
                } else {
                    AppSwal.error(data.message || '{{ __("website.failed_to_save_location") }}', '{{ __("website.error") }}');
                }
            })
            .catch(error => {
                console.error('Error saving location:', error);
                AppSwal.error('{{ __("website.something_went_wrong") }}', '{{ __("website.error") }}');
            });
        }

        // Load saved location if exists
        if (userLocation) {
            loadHeaderStates();
            setTimeout(function() {
                if (userLocation.state_id) {
                    const stateSelect = document.getElementById('header_state');
                    stateSelect.value = userLocation.state_id;
                    stateSelect.dispatchEvent(new Event('change'));

                    setTimeout(function() {
                        if (userLocation.city_id) {
                            const citySelect = document.getElementById('header_city');
                            citySelect.value = userLocation.city_id;
                            // Update Select2 if it exists
                            if ($(citySelect).hasClass('select2-hidden-accessible')) {
                                $(citySelect).trigger('change.select2');
                            } else {
                                citySelect.dispatchEvent(new Event('change'));
                            }

                            // Load branches if pickup is selected
                            const menuType = '{{ session("menu_type", "delivery") }}';
                            if (menuType === 'pickup') {
                                setTimeout(function() {
                                    loadHeaderBranches(userLocation.city_id);
                                }, 800);
                            }
                        }
                    }, 500);
                }
            }, 500);
        }

    });

    // Global function to validate city selection before adding to cart
    function validateCitySelection() {
        const userLocation = @json(session('user_location'));

        // Check if city is selected
        if (!userLocation || !userLocation.city_id) {
            // Show validation message
            AppSwal.confirm({
                icon: 'warning',
                title: '{{ __("website.error") }}',
                text: '{{ __("website.please_select_state_and_city") ?? "Please select state and city" }}',
                confirmButtonText: '{{ __("website.select_location") ?? "Select Location" }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Open location modal
                    const modalEl = document.getElementById('locationModal');
                    if (modalEl) {
                        let modal = bootstrap.Modal.getInstance(modalEl);
                        if (!modal) {
                            modal = new bootstrap.Modal(modalEl);
                        }
                        modal.show();
                        // Load states when modal is shown
                        setTimeout(function() {
                            if (typeof loadHeaderStates === 'function') {
                                loadHeaderStates();
                            }
                            @auth('web')
                            if (typeof loadHeaderAddresses === 'function') {
                                loadHeaderAddresses();
                            }
                            @endauth
                        }, 300);
                    }
                }
            });
            return false;
        }
        return true;
    }
</script>




