@extends('website.layout.master')
@section('title', __('website.checkout'))
@section('body', 'bg-white grey_mob')
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
                        <a href="{{ route('cart.index') }}">{{ __('website.cart') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ __('website.checkout') }}
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Checkout Section -->
    <section class="pickup_section secPadding pt_sm_0 pt-5">
        <div class="container px-lg-0">
            <div class="row">
                <div class="mealCol_wrap col-12 col-lg-8 mx-lg-auto">
                    <div class="DPick_Wrap">
                        <form action="{{ route('checkout.store') }}" method="POST" class="DPick_form"
                              id="checkout-form">
                            @csrf
                            <input type="hidden" name="order_type" value="{{ $orderType }}">
                            <ul style="display: none;" class="nav nav-pills basket_pills mbttom_40">
                                <li class="nav-item">
                                    <a class="nav-link {{ $orderType === 'delivery' ? 'active' : '' }}"
                                       id="delivery_tab">{{ __('website.delivery') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ ($orderType === 'pickup' || $orderType === 'pick_up') ? 'active' : '' }}" id="pickup_tab">
                                        {{ __('website.pick_up') }}</a>
                                </li>
                            </ul>

                            @if($orderType === 'delivery')
                                <!-- Delivery Information -->
                                <div class="">
                                    @guest('web')
                                        <div class="DPick_formDIV">
                                            <iframe width="100%"
                                                    height="350"
                                                    style="border:0; border-radius: 8px;"
                                                    allowfullscreen
                                                    loading="lazy"
                                                    src="https://www.google.com/maps?q={{ session('lat') }},{{ session('longitude') }}&hl=en&z=15&output=embed">
                                            </iframe>
                                        </div>
                                        <!-- Guest Contact Information -->
                                        <div class="DPick_formDIV">
                                            <div class="row">
                                                <div class="col-12 col-lg-6 mb-3">
                                                    <label for="guest_name" class="login_label">
                                                        {{ __('website.full_name') }} <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="form_group mbttom_30">
                                                        <input type="text" name="guest_name" id="guest_name"
                                                               class="login_input"
                                                               placeholder="{{ __('website.enter_your_full_name') }}"
                                                               value="{{ old('guest_name') }}"
                                                               required>
                                                        <i class="name_icon absinput_icon"></i>
                                                    </div>
                                                    @error('guest_name')
                                                    <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>

                                                <div class="col-12 col-lg-6 mb-3">
                                                    <label for="guest_phone" class="login_label">
                                                        {{ __('website.mobile_no') }} <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="form_group mbttom_30 position-relative">
                                                        <div class="d-flex">
                                                        @php
                                                            $countries = \App\Models\Location::where('type', 'country')->where('active', true)->orderBy('id')->get();
                                                            $firstCountry = $countries->first();
                                                            $selectedCountryId = old('guest_country_id', $firstCountry ? $firstCountry->id : '');
                                                        @endphp
                                                            <select name="guest_country_id" id="guest_country_id" class="form-select" style="width: 120px; border-radius: 8px 0 0 8px; border-right: none;">
                                                                @foreach($countries as $country)
                                                                    <option value="{{ $country->id }}" data-phone-code="{{ $country->phone_code }}" {{ $selectedCountryId == $country->id ? 'selected' : '' }}>
                                                                        {{ $country->phone_code }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        <input type="tel" name="guest_phone" id="guest_phone"
                                                                   class="login_input"
                                                               placeholder="99999999"
                                                               value="{{ old('guest_phone') }}"
                                                               maxlength="8"
                                                                   style="border-radius: 0 8px 8px 0; flex: 1;"
                                                               required>
                                                            <i class="phone_icon absinput_icon" style="right: 70px;"></i>
                                                            <button type="button" class="main_bttn mid_bttn position-absolute"
                                                                    id="send-otp-btn"
                                                                    style="right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;"
                                                                    disabled>
                                                            {{ __('website.send_otp') }}
                                                        </button>
                                                    </div>
                                                    </div>
                                                    <small id="phone-status" class="text-muted"></small>
                                                    @error('guest_phone')
                                                    <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                    @error('guest_country_id')
                                                    <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>

                                                <div class="col-12 col-lg-6 mb-3">
                                                    <label for="guest_email" class="login_label">
                                                        {{ __('website.email') }} <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="form_group mbttom_30">
                                                        <input type="email" name="guest_email" id="guest_email"
                                                               class="login_input"
                                                               placeholder="{{ __('website.enter_your_email') }}"
                                                               value="{{ old('guest_email') }}"
                                                               required>
                                                        <i class="email_icon absinput_icon"></i>
                                                    </div>
                                                    @error('guest_email')
                                                    <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                        </div>


                                                <div class="haveB_account">{{ __('website.already_have_account') }} <a
                                                        href="{{ route('website.login') }}">{{ __('website.login') }}</a></div>
                                            </div>
                                </div>
                                    @else
                                        @include('website.checkout.partials.addresses')
                                    @endguest
                                </div>
                            @else
                                <!-- Pickup Information -->
                                <!-- Branch Selection (using session data) -->
                                <div class="DPick_formDIV">
                                    <h3 class="asideSM_title">{{ __('website.selected_branch') ?? 'الفرع المختار' }}</h3>

                                    @if($selectedBranch)
                                        <!-- Display Selected Branch Info -->
                                        <div class="alert alert-info mb-3" style="border-left: 4px solid #17a2b8;">
                                            <div class="mt-2">
                                                <h5 class="mb-2" style="color: #0c5460;">
                                                    <i class="fa fa-store me-2"></i>{{ $selectedBranch->name }}
                                                </h5>
                                                <p class="mb-1">
                                                    <i class="fa fa-map-marker-alt me-2"></i>
                                                    <strong>{{ __('website.address') ?? 'العنوان' }}:</strong>
                                                    {{ $selectedBranch->address }}
                                                </p>
                                                @if($selectedBranch->phone)
                                                    <p class="mb-0">
                                                        <i class="fa fa-phone me-2"></i>
                                                        <strong>{{ __('website.phone') ?? 'الهاتف' }}:</strong>
                                                        {{ $selectedBranch->phone }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Hidden input to ensure branch_id is sent -->
                                        <input type="hidden" name="branch_id" value="{{ $selectedBranch->id }}">

                                        <!-- Option to change branch if other branches available -->
                                        @if($branches->count() > 1)
                                            <div class="mb-3">
                                                <a href="{{ route('website.home') }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa fa-edit me-1"></i> {{ __('website.change_branch') ?? 'تغيير الفرع' }}
                                                </a>
                                            </div>
                                        @endif
                                    @else
                                        <!-- No branch selected - redirect to home to select branch -->
                                        <div class="alert alert-warning">
                                            <p>{{ __('website.please_select_branch_first') ?? 'من فضلك اختر الفرع أولًا' }}</p>
                                            <a href="{{ route('website.home') }}" class="main_bttn mid_bttn">
                                                {{ __('website.select_branch') ?? 'اختر الفرع' }}
                                            </a>
                                        </div>

                                        @if($branches->count() > 0)
                                            <!-- Show available branches as fallback -->
                                            <div class="mt-3">
                                                <label for="branch_id" class="login_label">
                                                    {{ __('website.select_branch') ?? 'اختر الفرع' }} <span class="text-danger">*</span>
                                                </label>
                                                <div class="formSc_group formSc_home">
                                                    <select name="branch_id" id="branch_id" class="datePick_input" required>
                                                        <option value="">{{ __('website.choose_branch') ?? 'اختر الفرع' }}</option>
                                                        @foreach($branches as $branch)
                                                            <option value="{{ $branch->id }}">
                                                                {{ $branch->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>

                                <!-- Scheduled Pickup Date and Time -->
                                <div class="DPick_formDIV">
                                    <h4 class="asideSM_title">{{ __('website.pick_up_time') }}</h4>
                                    <label class="DPick_label">
                                        <input type="radio" name="meal_type" value="asap" class="DPick_checkbox" checked>
                                        <span>{{ __('website.as_soon_as_possible') }}</span>
                                    </label>
                                    <label class="DPick_label Schedule_label">
                                        <input type="radio" name="meal_type" value="scheduled" class="DPick_checkbox schedule_radio">
                                        <span>{{ __('website.schedule_order') }}</span>
                                    </label>
                                    <label class="DPick_label">
                                        <input type="radio" name="meal_type" value="dine_in" class="DPick_checkbox">
                                        <span>{{ __('website.dine_in') }}</span>
                                    </label>
                                    <div class="row dateTime_row" style="display: none;">
                                        <div class="col-12 col-lg-6">
                                            <div class="formSc_group">
                                                <label for="scheduled_date" class="login_label">
                                                    {{ __('website.pickup_date') ?? 'تاريخ الاستلام' }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="date" name="scheduled_date" id="scheduled_date" class="datePick_input scheduled-input" min="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <div class="formSc_group">
                                                <label for="scheduled_time" class="login_label">
                                                    {{ __('website.pickup_time') ?? 'وقت الاستلام' }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="time" name="scheduled_time" id="scheduled_time" class="datePick_input scheduled-input">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @guest('web')
                                    <div class="DPick_formDIV">
                                        <div class="row">
                                            <div class="col-12 col-lg-6 mb-3">
                                                <label for="guest_name_pickup" class="login_label">
                                                    {{ __('website.full_name') }} <span class="text-danger">*</span>
                                                </label>
                                                <div class="form_group mbttom_30">
                                                    <input type="text" name="guest_name" id="guest_name_pickup"
                                                           class="login_input"
                                                           placeholder="{{ __('website.enter_your_full_name') }}"
                                                           value="{{ old('guest_name') }}"
                                                           required>
                                                    <i class="name_icon absinput_icon"></i>
                                                </div>
                                                @error('guest_name')
                                                <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

                                            <div class="col-12 col-lg-6 mb-3">
                                                <label for="guest_phone_pickup" class="login_label">
                                                    {{ __('website.mobile_no') }} <span class="text-danger">*</span>
                                                </label>
                                                <div class="form_group mbttom_30 position-relative">
                                                    <div class="d-flex">
                                                    @php
                                                        $countriesPickup = \App\Models\Location::where('type', 'country')->where('active', true)->orderBy('id')->get();
                                                        $firstCountryPickup = $countriesPickup->first();
                                                        $selectedCountryIdPickup = old('guest_country_id', $firstCountryPickup ? $firstCountryPickup->id : '');
                                                    @endphp
                                                        <select name="guest_country_id" id="guest_country_id_pickup" class="form-select" style="width: 120px; border-radius: 8px 0 0 8px; border-right: none;">
                                                            @foreach($countriesPickup as $country)
                                                                <option value="{{ $country->id }}" data-phone-code="{{ $country->phone_code }}" {{ $selectedCountryIdPickup == $country->id ? 'selected' : '' }}>
                                                                    {{ $country->phone_code }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    <input type="tel" name="guest_phone" id="guest_phone_pickup"
                                                               class="login_input"
                                                           placeholder="99999999"
                                                           value="{{ old('guest_phone') }}"
                                                           maxlength="8"
                                                               style="border-radius: 0 8px 8px 0; flex: 1;"
                                                           required>
                                                        <i class="phone_icon absinput_icon" style="right: 70px;"></i>
                                                        <button type="button" class="main_bttn mid_bttn position-absolute"
                                                                id="send-otp-btn-pickup"
                                                                style="right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;"
                                                                disabled>
                                                        {{ __('website.send_otp') }}
                                                    </button>
                                                </div>
                                                </div>
                                                <small id="phone-status-pickup" class="text-muted"></small>
                                                @error('guest_phone')
                                                <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                                @error('guest_country_id')
                                                <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

                                            <div class="col-12 col-lg-6 mb-3">
                                                <label for="guest_email_pickup" class="login_label">
                                                    {{ __('website.email') }} <span class="text-danger">*</span>
                                                </label>
                                                <div class="form_group mbttom_30">
                                                    <input type="email" name="guest_email" id="guest_email_pickup"
                                                           class="login_input"
                                                           placeholder="{{ __('website.enter_your_email') }}"
                                                           value="{{ old('guest_email') }}"
                                                           required>
                                                    <i class="email_icon absinput_icon"></i>
                                                </div>
                                                @error('guest_email')
                                                <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="haveB_account">{{ __('website.already_have_account') }} <a
                                                href="{{ route('website.login') }}">{{ __('website.login') }}</a></div>
                                    </div>
                                @endguest
                            @endif

                            <!-- Order Summary -->
                            <div class="DPick_formDIV" style="display:none">
                                <h3 class="asideSM_title">
                                    <i class="fa fa-receipt me-2"></i>
                                    {{ __('website.order_summary') }}
                                </h3>

                                <!-- Cart Items -->
                                <div class="mb-3">
                                    @foreach($cartProducts as $item)
                                        <div class="py-2 border-bottom">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $item['image'] }}"
                                                         alt="{{ $item['name'] }}"
                                                         class="rounded me-2"
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                    <div>
                                                        <strong>{{ $item['name'] }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ __('website.quantity') }}: {{ $item['quantity'] }}</small>
                                                    </div>
                                                </div>
                                                <span class="fw-bold">{{ number_format((float)$item['price'], 3) }} {{ session('currency', 'KD') }}</span>
                                            </div>
                                            @if(!empty($item['addons']))
                                                <div class="ms-5 mt-1">
                                                    @foreach($item['addons'] as $addon)
                                                        <small class="text-muted d-block">+ {{ $addon['name'] }}</small>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if(!empty($item['is_box']) && !empty($item['box_addons']))
                                                <div class="ms-5 mt-1">
                                                    @foreach($item['box_addons'] as $sub)
                                                        <small class="text-muted d-block">{{ $sub['sub_product_name'] }}</small>
                                                        @foreach(($sub['addons'] ?? []) as $addon)
                                                            <small class="text-muted d-block ms-3">+ {{ $addon['name'] }}</small>
                                                        @endforeach
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Totals -->
                                <div class="DPick_ftotal">
                                    <span>{{ __('website.subtotal') }}</span>
                                    <span>{{ number_format((float)$subtotal, 3) }} {{ session('currency', 'KD') }}</span>
                                </div>

                                @if($voucherDiscount > 0)
                                    <div class="DPick_ftotal text-success">
                                        <span>{{ __('website.discount') }}</span>
                                        <span>-{{ number_format((float)$voucherDiscount, 3) }} {{ session('currency', 'KD') }}</span>
                                    </div>
                                @endif

                                @if($orderType === 'delivery')
                                    <div class="DPick_ftotal">
                                        <span>{{ __('website.delivery_cost') }}</span>
                                        <span>
                                            @if($deliveryCost > 0)
                                                {{ number_format((float)$deliveryCost, 3) }} {{ session('currency', 'KD') }}
                                            @else
                                                <span style="color: #28a745; font-weight: bold;">{{ __('website.free_delivery') }}</span>
                                            @endif
                                        </span>
                                    </div>
                                @endif

                                <div class="DPick_ftotal" style="font-size: 1.2rem; font-weight: bold;">
                                    <span>{{ __('website.total') }}</span>
                                    <span>{{ number_format((float)$total, 3) }} {{ session('currency', 'KD') }}</span>
                                </div>
                            </div>
                            <div class="DPick_formDIV">
                                <button type="submit"
                                        id="checkout-submit-btn"
                                        class="main_bttn check_bttn  w-100 hvr-sweep-to-right mrgTop_wide"
                                        @if($orderType === 'delivery' && auth('web')->check() && (!isset($hasAvailableAddress) || !$hasAvailableAddress))
                                            disabled
                                            style="opacity: 0.6; cursor: not-allowed;"
                                        @endif>
                                    {{ __('website.next') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- OTP Verification Modal -->
    <div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="otpModalLabel">{{ __('website.verify_otp') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('website.enter_otp_sent_to_phone') }}</p>
                    <div id="otp-display-container" style="display: none; background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; text-align: center;">
                        <strong style="color: #28a745; font-size: 18px;">{{ __('website.your_otp_code') }}:</strong>
                        <div id="otp-display" style="font-size: 32px; font-weight: bold; color: #007bff; letter-spacing: 8px; margin-top: 10px;"></div>
                        <small class="text-muted">{{ __('website.this_otp_is_for_testing_only') }}</small>
                    </div>
                    <div class="form-group mb-3">
                        <label for="otp_code">{{ __('website.otp_code') }}</label>
                        <input type="text" id="otp_code" class="form-control text-center"
                               placeholder="000000" maxlength="6" style="font-size: 24px; letter-spacing: 8px;">
                    </div>
                    <div class="resend-otp-container">
                        <button type="button" class="btn btn-link" id="resend-otp-btn">
                            {{ __('website.resend_otp') }}
                        </button>
                        <span id="resend-timer" class="text-muted" style="display: none; margin-left: 10px;">
                            (<span id="timer-countdown">60</span> {{ __('website.seconds') }})
                        </span>
                    </div>
                    <div id="otp-error" class="text-danger mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('website.cancel') }}</button>
                    <button type="button" class="main_bttn mid_bttn" id="verify-otp-btn">{{ __('website.verify') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Setup Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordModalLabel">{{ __('website.set_password') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('website.please_set_password_for_account') }}</p>
                    <div class="form-group mb-3">
                        <label for="password">{{ __('website.password') }} <span class="text-danger">*</span></label>
                        <input type="password" id="password" class="form-control"
                               placeholder="{{ __('website.enter_password') }}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="password_confirmation">{{ __('website.confirm_password') }} <span class="text-danger">*</span></label>
                        <input type="password" id="password_confirmation" class="form-control"
                               placeholder="{{ __('website.confirm_password') }}" required>
                    </div>
                    <div id="password-error" class="text-danger mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('website.cancel') }}</button>
                    <button type="button" class="main_bttn mid_bttn" id="register-guest-btn">{{ __('website.create_account') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('website-footer')
    <script>
        let otpVerified = false;
        let accountCreated = false;
        let resendOtpTimer = null;
        let resendOtpCountdown = 60; // 60 seconds

        // Schedule order toggle
        $('.schedule_radio').on('change', function () {
            if ($(this).is(':checked')) {
                $('.dateTime_row').slideDown();
                // Make scheduled date and time required
                $('#scheduled_date, #scheduled_time').prop('required', true);
            }
        });

        $('input[name="meal_type"][value="asap"]').on('change', function () {
            if ($(this).is(':checked')) {
                $('.dateTime_row').slideUp();
                // Remove required from scheduled date and time
                $('#scheduled_date, #scheduled_time').prop('required', false).val('');
            }
        });

        $('input[name="meal_type"][value="dine_in"]').on('change', function () {
            if ($(this).is(':checked')) {
                $('.dateTime_row').slideUp();
                // Remove required from scheduled date and time
                $('#scheduled_date, #scheduled_time').prop('required', false).val('');
            }
        });

        // Enable send OTP button when phone and country are selected (Delivery)
        $('#guest_phone, #guest_country_id').on('input change', function() {
            const phone = $('#guest_phone').val() ? $('#guest_phone').val().trim() : '';
            const countryId = $('#guest_country_id').val();
            if (phone.length === 8 && countryId) {
                $('#send-otp-btn').prop('disabled', false);
            } else {
                $('#send-otp-btn').prop('disabled', true);
            }
        });

        // Enable send OTP button when phone and country are selected (Pickup)
        $('#guest_phone_pickup, #guest_country_id_pickup').on('input change', function() {
            const phone = $('#guest_phone_pickup').val() ? $('#guest_phone_pickup').val().trim() : '';
            const countryId = $('#guest_country_id_pickup').val();
            if (phone.length === 8 && countryId) {
                $('#send-otp-btn-pickup').prop('disabled', false);
            } else {
                $('#send-otp-btn-pickup').prop('disabled', true);
            }
        });

        // Auto-enable OTP button on page load if country is pre-selected (Delivery)
        $(document).ready(function() {
            const phone = $('#guest_phone').val() ? $('#guest_phone').val().trim() : '';
            const countryId = $('#guest_country_id').val();
            if (phone.length === 8 && countryId) {
                $('#send-otp-btn').prop('disabled', false);
            }
        });

        // Auto-enable OTP button on page load if country is pre-selected (Pickup)
        $(document).ready(function() {
            const phone = $('#guest_phone_pickup').val() ? $('#guest_phone_pickup').val().trim() : '';
            const countryId = $('#guest_country_id_pickup').val();
            if (phone.length === 8 && countryId) {
                $('#send-otp-btn-pickup').prop('disabled', false);
            }
        });

        // Check phone and email before sending OTP (Delivery)
        $('#send-otp-btn').on('click', function() {
            const phone = $('#guest_phone').val() ? $('#guest_phone').val().trim() : '';
            const countryId = $('#guest_country_id').val();
            const email = $('#guest_email').val() ? $('#guest_email').val().trim() : '';

            if (!phone || phone.length !== 8) {
                Swal.fire({
                    icon: 'warning',
                    title: '{{ __("website.error") }}',
                    text: '{{ __("website.phone_must_be_8_digits") }}'
                });
                return;
            }

            if (!countryId) {
                Swal.fire({
                    icon: 'warning',
                    title: '{{ __("website.error") }}',
                    text: '{{ __("website.please_select_country") }}'
                });
                return;
            }

            if (!email) {
                Swal.fire({
                    icon: 'warning',
                    title: '{{ __("website.error") }}',
                    text: '{{ __("website.please_enter_email") }}'
                });
                return;
            }

            // Check if phone/email exists
            $.ajax({
                url: "{{ route('checkout.check-phone') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    phone: phone,
                    guest_country_id: countryId,
                    email: email
                },
                beforeSend: function() {
                    $('#send-otp-btn').prop('disabled', true).text('{{ __("website.checking") }}...');
                },
                success: function(response) {
                    if (response.status) {
                        // Phone and email are available, send OTP
                        sendOtp(phone, countryId, 'delivery');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("website.error") }}',
                            text: response.message || '{{ __("website.phone_or_email_exists") }}'
                        });
                        $('#send-otp-btn').prop('disabled', false).text('{{ __("website.send_otp") }}');
                    }
                },
                error: function(xhr) {
                    let errorMsg = '{{ __("website.something_went_wrong") }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("website.error") }}',
                        text: errorMsg
                    });
                    $('#send-otp-btn').prop('disabled', false).text('{{ __("website.send_otp") }}');
                }
            });
        });

        // Check phone and email before sending OTP (Pickup)
        $('#send-otp-btn-pickup').on('click', function() {
            const phone = $('#guest_phone_pickup').val() ? $('#guest_phone_pickup').val().trim() : '';
            const countryId = $('#guest_country_id_pickup').val();
            const email = $('#guest_email_pickup').val() ? $('#guest_email_pickup').val().trim() : '';

            if (!phone || phone.length !== 8) {
                Swal.fire({
                    icon: 'warning',
                    title: '{{ __("website.error") }}',
                    text: '{{ __("website.phone_must_be_8_digits") }}'
                });
                return;
            }

            if (!countryId) {
                Swal.fire({
                    icon: 'warning',
                    title: '{{ __("website.error") }}',
                    text: '{{ __("website.please_select_country") }}'
                });
                return;
            }

            if (!email) {
                Swal.fire({
                    icon: 'warning',
                    title: '{{ __("website.error") }}',
                    text: '{{ __("website.please_enter_email") }}'
                });
                return;
            }

            // Check if phone/email exists
            $.ajax({
                url: "{{ route('checkout.check-phone') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    phone: phone,
                    guest_country_id: countryId,
                    email: email
                },
                beforeSend: function() {
                    $('#send-otp-btn-pickup').prop('disabled', true).text('{{ __("website.checking") }}...');
                },
                success: function(response) {
                    if (response.status) {
                        // Phone and email are available, send OTP
                        sendOtp(phone, countryId, 'pickup');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("website.error") }}',
                            text: response.message || '{{ __("website.phone_or_email_exists") }}'
                        });
                        $('#send-otp-btn-pickup').prop('disabled', false).text('{{ __("website.send_otp") }}');
                    }
                },
                error: function(xhr) {
                    let errorMsg = '{{ __("website.something_went_wrong") }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("website.error") }}',
                        text: errorMsg
                    });
                    $('#send-otp-btn-pickup').prop('disabled', false).text('{{ __("website.send_otp") }}');
                }
            });
        });

        // Send OTP
        function sendOtp(phone, countryId, type = 'delivery') {
            const email = type === 'pickup' ? ($('#guest_email_pickup').val() ? $('#guest_email_pickup').val().trim() : '') : ($('#guest_email').val() ? $('#guest_email').val().trim() : '');
            const sendBtnId = type === 'pickup' ? '#send-otp-btn-pickup' : '#send-otp-btn';
            const phoneStatusId = type === 'pickup' ? '#phone-status-pickup' : '#phone-status';

            $.ajax({
                url: "{{ route('checkout.send-otp') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    phone: phone,
                    guest_country_id: countryId,
                    email: email
                },
                success: function(response) {
                    if (response.status) {
                        let message = response.message;

                        // Show appropriate message based on how OTP was sent
                        if (response.sent_via === 'email') {
                            message += '\n{{ __("website.check_your_email") }}';
                        }

                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("website.success") }}',
                            text: message,
                            timer: 2000,
                            showConfirmButton: true
                        });

                        // Show OTP in popup if available
                        if (response.show_otp && response.otp) {
                            $('#otp-display').text(response.otp);
                            $('#otp-display-container').show();
                            // Auto-fill OTP in input field
                            $('#otp_code').val(response.otp);
                        } else {
                            $('#otp-display-container').hide();
                        }

                        // Start resend OTP timer
                        startResendOtpTimer();

                        $('#otpModal').modal('show');
                        $(phoneStatusId).text(response.message || '{{ __("website.otp_sent_successfully") }}').removeClass('text-danger').addClass('text-success');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("website.error") }}',
                            text: response.message
                        });
                        $(sendBtnId).prop('disabled', false).text('{{ __("website.send_otp") }}');
                    }
                },
                error: function(xhr) {
                    let errorMsg = '{{ __("website.something_went_wrong") }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("website.error") }}',
                        text: errorMsg
                    });
                    $(sendBtnId).prop('disabled', false).text('{{ __("website.send_otp") }}');
                }
            });
        }

        // Verify OTP
        $('#verify-otp-btn').on('click', function() {
            const otp = $('#otp_code').val() ? $('#otp_code').val().trim() : '';

            if (otp.length !== 6) {
                $('#otp-error').text('{{ __("website.please_enter_valid_otp") }}');
                return;
            }

            $.ajax({
                url: "{{ route('checkout.verify-otp') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    otp: otp
                },
                beforeSend: function() {
                    $('#verify-otp-btn').prop('disabled', true).text('{{ __("website.verifying") }}...');
                    $('#otp-error').text('');
                },
                success: function(response) {
                    if (response.status) {
                        otpVerified = true;
                        $('#otpModal').modal('hide');
                        $('#passwordModal').modal('show');
                        // Update phone status for both delivery and pickup
                        const orderType = $('input[name="order_type"]').val();
                        if (orderType === 'pickup' || orderType === 'pick_up') {
                            $('#phone-status-pickup').text('{{ __("website.otp_verified") }}').removeClass('text-danger').addClass('text-success');
                        } else {
                            $('#phone-status').text('{{ __("website.otp_verified") }}').removeClass('text-danger').addClass('text-success');
                        }
                    } else {
                        $('#otp-error').text(response.message || '{{ __("website.invalid_otp") }}');
                        $('#verify-otp-btn').prop('disabled', false).text('{{ __("website.verify") }}');
                    }
                },
                error: function(xhr) {
                    let errorMsg = '{{ __("website.something_went_wrong") }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    $('#otp-error').text(errorMsg);
                    $('#verify-otp-btn').prop('disabled', false).text('{{ __("website.verify") }}');
                }
            });
        });

        // Function to start resend OTP timer
        function startResendOtpTimer() {
            // Clear existing timer if any
            if (resendOtpTimer) {
                clearInterval(resendOtpTimer);
            }

            // Reset countdown
            resendOtpCountdown = 60;

            // Disable resend button
            $('#resend-otp-btn').prop('disabled', true);
            $('#resend-timer').show();
            $('#timer-countdown').text(resendOtpCountdown);

            // Start countdown
            resendOtpTimer = setInterval(function() {
                resendOtpCountdown--;
                $('#timer-countdown').text(resendOtpCountdown);

                if (resendOtpCountdown <= 0) {
                    // Enable resend button
                    clearInterval(resendOtpTimer);
                    resendOtpTimer = null;
                    $('#resend-otp-btn').prop('disabled', false);
                    $('#resend-timer').hide();
                }
            }, 1000);
        }

        // Resend OTP
        $('#resend-otp-btn').on('click', function() {
            // Determine which form is active (delivery or pickup)
            const orderType = $('input[name="order_type"]').val();
            let phone, countryId, type;

            if (orderType === 'pickup' || orderType === 'pick_up') {
                phone = $('#guest_phone_pickup').val() ? $('#guest_phone_pickup').val().trim() : '';
                countryId = $('#guest_country_id_pickup').val();
                type = 'pickup';
            } else {
                phone = $('#guest_phone').val() ? $('#guest_phone').val().trim() : '';
                countryId = $('#guest_country_id').val();
                type = 'delivery';
            }

            if (phone && countryId && !$(this).prop('disabled')) {
                sendOtp(phone, countryId, type);
            }
        });

        // Clear timer when modal is closed
        $('#otpModal').on('hidden.bs.modal', function() {
            if (resendOtpTimer) {
                clearInterval(resendOtpTimer);
                resendOtpTimer = null;
            }
            $('#resend-otp-btn').prop('disabled', false);
            $('#resend-timer').hide();
            resendOtpCountdown = 60;
        });

        // Register guest user
        $('#register-guest-btn').on('click', function() {
            const name = $('#guest_name').val() ? $('#guest_name').val().trim() : '';
            const phone = $('#guest_phone').val() ? $('#guest_phone').val().trim() : '';
            const countryId = $('#guest_country_id').val();
            const email = $('#guest_email').val() ? $('#guest_email').val().trim() : '';
            const password = $('#password').val();
            const passwordConfirmation = $('#password_confirmation').val();

            if (!password || password.length < 6) {
                $('#password-error').text('{{ __("website.password_min_6_characters") }}');
                return;
            }

            if (password !== passwordConfirmation) {
                $('#password-error').text('{{ __("website.passwords_do_not_match") }}');
                return;
            }

            $.ajax({
                url: "{{ route('checkout.register-guest') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    name: name,
                    phone: phone,
                    guest_country_id: countryId,
                    email: email,
                    password: password,
                    password_confirmation: passwordConfirmation
                },
                beforeSend: function() {
                    $('#register-guest-btn').prop('disabled', true).text('{{ __("website.creating") }}...');
                    $('#password-error').text('');
                },
                success: function(response) {
                    if (response.status) {
                        accountCreated = true;
                        $('#passwordModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("website.success") }}',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload page to show authenticated user view
                            location.reload();
                        });
                    } else {
                        $('#password-error').text(response.message || '{{ __("website.registration_failed") }}');
                        $('#register-guest-btn').prop('disabled', false).text('{{ __("website.create_account") }}');
                    }
                },
                error: function(xhr) {
                    let errorMsg = '{{ __("website.something_went_wrong") }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        if (errors.length > 0) {
                            errorMsg = errors[0];
                        }
                    }
                    $('#password-error').text(errorMsg);
                    $('#register-guest-btn').prop('disabled', false).text('{{ __("website.create_account") }}');
                }
            });
        });


        // Check restaurant status before form submit
        @if(isset($restaurantIsClosed) && $restaurantIsClosed)
            $(document).ready(function() {
                AppSwal.error('{{ __("website.restaurant_is_closed") ?? "المطعم مغلق حالياً ولا يمكن إتمام الطلب" }}');
            });
        @endif

        // Prevent form submission if not verified
        $('#checkout-form').on('submit', function(e) {
            @if(isset($restaurantIsClosed) && $restaurantIsClosed)
                e.preventDefault();
                AppSwal.error('{{ __("website.restaurant_is_closed") ?? "المطعم مغلق حالياً ولا يمكن إتمام الطلب" }}');
                return false;
            @endif

            @guest('web')
            if (!otpVerified || !accountCreated) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: '{{ __("website.verification_required") }}',
                    text: '{{ __("website.please_verify_phone_and_create_account") }}'
                });
                return false;
            }
            @endguest

            // Validate pickup requirements
            if ('{{ $orderType }}' === 'pick_up' || '{{ $orderType }}' === 'pickup') {
                const branchId = $('input[name="branch_id"]').val() || $('#branch_id').val();
                if (!branchId) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: '{{ __("website.error") }}',
                        text: '{{ __("website.please_select_branch_first") ?? "من فضلك اختر الفرع أولًا" }}'
                    });
                    return false;
                }

                // Validate scheduled pickup date/time if scheduled is selected
                const mealType = $('input[name="meal_type"]:checked').val();
                if (mealType === 'scheduled') {
                    const scheduledDate = $('#scheduled_date').val();
                    const scheduledTime = $('#scheduled_time').val();

                    if (!scheduledDate || !scheduledTime) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: '{{ __("website.error") }}',
                            text: '{{ __("website.please_select_pickup_date_and_time") ?? "من فضلك اختر تاريخ ووقت الاستلام" }}'
                        });
                        return false;
                    }
                }
            }
        });

        // Pickup Branch Selection - Save branch to session when selected (if branch dropdown exists)
        @if($orderType === 'pick_up' || $orderType === 'pickup')
            // Save branch to session when selected (only if branch dropdown exists and is not hidden)
            $('#branch_id').on('change', function() {
                const branchId = $(this).val();

                if (!branchId) {
                    return;
                }

                $.ajax({
                    url: "{{ route('website.branches.save-pickup') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        branch_id: branchId
                    },
                    success: function(response) {
                        if (response.status && response.branch) {
                            // Reload page to show updated branch info
                            location.reload();
                        }
                    }
                });
            });
        @endif

        // Handle address selection for delivery orders
        @if($orderType === 'delivery' && auth('web')->check())
            // Function to update submit button state
            function updateSubmitButtonState() {
                const selectedAddress = $('input[name="address_id"]:checked');
                const submitBtn = $('#checkout-submit-btn');
                const hasAvailableAddress = {{ isset($hasAvailableAddress) && $hasAvailableAddress ? 'true' : 'false' }};

                // Check if there's any available address at all
                if (!hasAvailableAddress) {
                    // Disable submit button if no available addresses exist
                    submitBtn.prop('disabled', true).addClass('disabled').css('opacity', '0.6').css('cursor', 'not-allowed');
                    return;
                }

                if (selectedAddress.length && selectedAddress.data('available') === '0') {
                    // Disable submit button if unavailable address is selected
                    submitBtn.prop('disabled', true).addClass('disabled').css('opacity', '0.6').css('cursor', 'not-allowed');
                } else if (selectedAddress.length && selectedAddress.data('available') === '1') {
                    // Enable submit button if available address is selected
                    submitBtn.prop('disabled', false).removeClass('disabled').css('opacity', '1').css('cursor', 'pointer');
                } else {
                    // If no address is selected but available addresses exist, check if first available is auto-selected
                    const firstAvailable = $('input[name="address_id"][data-available="1"]:checked');
                    if (firstAvailable.length) {
                        submitBtn.prop('disabled', false).removeClass('disabled').css('opacity', '1').css('cursor', 'pointer');
                    } else {
                        submitBtn.prop('disabled', true).addClass('disabled').css('opacity', '0.6').css('cursor', 'not-allowed');
                    }
                }
            }

            // Initialize button state on page load
            updateSubmitButtonState();

            // Update button state and border when address selection changes
            $(document).on('change', 'input[name="address_id"]', function() {
                updateSubmitButtonState();

                // Remove selected class from all address cards
                $('.address_card').removeClass('address-selected');

                // Add selected class to the selected address card
                const selectedAddressId = $(this).val();
                $(this).closest('.address_card').addClass('address-selected');
            });

            // Initialize border for initially selected address
            $(document).ready(function() {
                const checkedRadio = $('input[name="address_id"]:checked');
                if (checkedRadio.length) {
                    checkedRadio.closest('.address_card').addClass('address-selected');
                }
            });

            // Prevent selection of unavailable addresses
            $(document).on('click', '.address-radio[data-available="0"]', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Check if this address is already selected
                if ($(this).is(':checked')) {
                    // If already selected, show warning
                    Swal.fire({
                        icon: 'warning',
                        title: '{{ __("website.address_out_of_service_area") ?? "العنوان خارج نطاق الخدمة" }}',
                        html: '{{ __("website.changing_address_will_clear_cart") ?? "هذا العنوان خارج نطاق الفرع المختار. سيتم مسح السلة إذا اخترت عنواناً مختلفاً عن العنوان المختار في البداية." }}',
                        showCancelButton: true,
                        confirmButtonText: '{{ __("website.clear_cart_and_continue") ?? "مسح السلة والمتابعة" }}',
                        cancelButtonText: '{{ __("website.cancel") ?? "إلغاء" }}',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Clear cart and redirect to home
                            $.ajax({
                                url: "{{ route('cart.clear') }}",
                                method: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function() {
                                    Swal.fire({
                                        icon: 'info',
                                        title: '{{ __("website.cart_cleared") ?? "تم مسح السلة" }}',
                                        text: '{{ __("website.please_select_new_city_and_add_products") ?? "يرجى اختيار المدينة الجديدة وإضافة المنتجات مرة أخرى" }}',
                                        confirmButtonText: '{{ __("website.ok") ?? "حسناً" }}'
                                    }).then(() => {
                                        window.location.href = "{{ route('website.home') }}";
                                    });
                                }
                            });
                        }
                    });
                } else {
                    // Try to select it (will be prevented by disabled attribute, but show message)
                    Swal.fire({
                        icon: 'warning',
                        title: '{{ __("website.address_out_of_service_area") ?? "العنوان خارج نطاق الخدمة" }}',
                        html: '{{ __("website.changing_address_will_clear_cart") ?? "هذا العنوان خارج نطاق الفرع المختار. سيتم مسح السلة إذا اخترت عنواناً مختلفاً عن العنوان المختار في البداية." }}',
                        showCancelButton: true,
                        confirmButtonText: '{{ __("website.clear_cart_and_continue") ?? "مسح السلة والمتابعة" }}',
                        cancelButtonText: '{{ __("website.cancel") ?? "إلغاء" }}',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Clear cart and redirect to home
                            $.ajax({
                                url: "{{ route('cart.clear') }}",
                                method: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function() {
                                    Swal.fire({
                                        icon: 'info',
                                        title: '{{ __("website.cart_cleared") ?? "تم مسح السلة" }}',
                                        text: '{{ __("website.please_select_new_city_and_add_products") ?? "يرجى اختيار المدينة الجديدة وإضافة المنتجات مرة أخرى" }}',
                                        confirmButtonText: '{{ __("website.ok") ?? "حسناً" }}'
                                    }).then(() => {
                                        window.location.href = "{{ route('website.home') }}";
                                    });
                                }
                            });
                        }
                    });
                }

                return false;
            });

            // Also prevent clicking on the label of unavailable addresses
            $(document).on('click', 'label[for^="address_"]', function(e) {
                const addressId = $(this).attr('for').replace('address_', '');
                const radio = $(`#address_${addressId}`);

                if (radio.length && radio.data('available') === '0') {
                    e.preventDefault();
                    radio.trigger('click');
                    return false;
                }
            });

            // Prevent form submission if unavailable address is selected
            $('#checkout-form').on('submit', function(e) {
                const selectedAddress = $('input[name="address_id"]:checked');

                if (selectedAddress.length && selectedAddress.data('available') === '0') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("website.invalid_address_selected") ?? "عنوان غير صالح" }}',
                        text: '{{ __("website.please_select_available_address") ?? "يرجى اختيار عنوان متاح في نطاق الخدمة" }}'
                    });
                    return false;
                }
            });
        @endif
    </script>
<!-- Add Address Modal (Moved from partial to avoid nested forms) -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title d-flex align-items-center gap-2" id="addAddressModalLabel">
                    <i class="fa-solid fa-map-location-dot text-warning"></i>
                    {{ __('website.add_new_address') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="add-address-form" action="{{ route('addresses.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4 pt-3">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('website.address_title') }}</label>
                            <div class="address-type-selector d-flex gap-2">
                                <input type="radio" class="btn-check" name="title" id="checkout_type_home" value="home" checked>
                                <label class="btn btn-outline-light flex-fill type-label" for="checkout_type_home">
                                    <i class="fa-solid fa-house mb-1 d-block"></i>
                                    {{ __('website.address_title_home') }}
                                </label>

                                <input type="radio" class="btn-check" name="title" id="checkout_type_work" value="work">
                                <label class="btn btn-outline-light flex-fill type-label" for="checkout_type_work">
                                    <i class="fa-solid fa-briefcase mb-1 d-block"></i>
                                    {{ __('website.address_title_work') }}
                                </label>

                                <input type="radio" class="btn-check" name="title" id="checkout_type_other" value="other">
                                <label class="btn btn-outline-light flex-fill type-label" for="checkout_type_other">
                                    <i class="fa-solid fa-location-dot mb-1 d-block"></i>
                                    {{ __('website.address_title_other') }}
                                </label>
                            </div>
                            <select class="d-none" id="checkout_address_title" name="title_hidden">
                                <option value="home">home</option>
                                <option value="work">work</option>
                                <option value="other">other</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check custom-check pb-2 ms-md-3">
                                <input class="form-check-input" type="checkbox" id="is_main_checkout" name="is_main" value="1">
                                <label class="form-check-label fw-bold" for="is_main_checkout">
                                    {{ __('website.set_as_main_address') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    @php
                        $defaultCountry = \App\Models\Location::where('type', 'country')->where('active', true)->orderBy('id')->first();
                    @endphp
                    <input type="hidden" id="checkout-country" name="country" value="{{ $defaultCountry?->getTranslation('name', app()->getLocale()) ?? '' }}">
                    <input type="hidden" id="checkout-country_id" name="country_id" value="{{ $defaultCountry?->id ?? '' }}">

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="checkout-state" class="form-label">{{ __('website.state') }}</label>
                            <select class="form-select premium-input" id="checkout-state" name="state">
                                <option value="">{{ __('website.select_state') ?? 'Select State' }}</option>
                            </select>
                            <input type="hidden" id="checkout-state_name" name="state_name">
                        </div>
                        <div class="col-md-6">
                            <label for="checkout-city" class="form-label">{{ __('website.city') }}</label>
                            <select class="form-select premium-input" id="checkout-city" name="city" disabled>
                                <option value="">{{ __('website.select_city') ?? 'Select City' }}</option>
                            </select>
                            <input type="hidden" id="checkout-city_name" name="city_name">
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="checkout-block" class="form-label">{{ __('website.block') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control premium-input" id="checkout-block" name="block" required>
                        </div>
                        <div class="col-md-4">
                            <label for="checkout-street" class="form-label">{{ __('website.street') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control premium-input" id="checkout-street" name="street" required>
                        </div>
                        <div class="col-md-4">
                            <label for="checkout-avenue" class="form-label">{{ __('website.avenue') }}</label>
                            <input type="text" class="form-control premium-input" id="checkout-avenue" name="avenue">
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="checkout-building" class="form-label">{{ __('website.building') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control premium-input" id="checkout-building" name="building" required>
                        </div>
                        <div class="col-md-4">
                            <label for="checkout-floor" class="form-label">{{ __('website.floor') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control premium-input" id="checkout-floor" name="floor" required>
                        </div>
                        <div class="col-md-4">
                            <label for="checkout-apartment" class="form-label">{{ __('website.apartment') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control premium-input" id="checkout-apartment" name="apartment" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="checkout-additional_directions" class="form-label">{{ __('website.additional_directions') }}</label>
                        <textarea class="form-control premium-input" id="checkout-additional_directions" name="additional_directions" rows="2" placeholder="{{ __('website.additional_directions_placeholder') ?? 'Any extra details...' }}"></textarea>
                    </div>
                    
                    <input type="hidden" name="latitude" id="checkout-latitude">
                    <input type="hidden" name="longitude" id="checkout-longitude">
                    
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label mb-0 fw-bold">{{ __('website.select_location_on_map') ?? 'Select Location on Map' }}</label>
                            <button type="button" id="locate-me-checkout" class="btn btn-dark premium-modal-btn btn-sm">
                                <i class="fa-solid fa-location-crosshairs me-1"></i> {{ __('website.locate_me') ?? 'Locate Me' }}
                            </button>
                        </div>
                        <div id="checkout-address-map-container" class="position-relative overflow-hidden border">
                            <input type="text" id="checkout-address-search" class="form-control premium-input map-search-box" 
                                   placeholder="{{ __('website.search_location') ?? 'Search for a location...' }}" 
                                   autocomplete="new-password">
                            <div id="checkout-address-map" style="width: 100%; height: 100%;"></div>
                        </div>
                    </div>
                    
                    <div id="checkout-address-form-error" class="alert alert-danger mt-3" style="display: none; border-radius: 12px;"></div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-light premium-modal-btn" data-bs-dismiss="modal">{{ __('website.cancel') }}</button>
                    <button type="submit" class="btn btn-warning premium-modal-btn bg-gold text-dark border-0" id="save-address-btn">{{ __('website.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
    <script>
    // Initialize modal handling for address popup (moved from partial to here to ensure jQuery is loaded)
    $(document).ready(function() {
        // Use event delegation for the button since it might be loaded dynamically or late
        $(document).on('click', '#add-address-btn, #add-address-btn-empty', function(e) {
             e.preventDefault();
             e.stopPropagation();
             console.log('Add address button clicked'); // Debug
             var modalEl = document.getElementById('addAddressModal');
             if(modalEl) {
                console.log('Modal element found'); // Debug
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
                // Initialize map after modal is shown to ensure correct rendering
                // Wait for Google Maps to be loaded
                function tryInitMap() {
                    if (typeof google !== 'undefined' && typeof window.initCheckoutMap === 'function') {
                        window.initCheckoutMap();
                    } else {
                        setTimeout(tryInitMap, 100);
                    }
                }
                setTimeout(tryInitMap, 300);
                loadCheckoutStates();
             } else {
                 console.error('Address modal not found');
             }
        });

        // Load States
        function loadCheckoutStates() {
            const stateSelect = $('#checkout-state');
            if (stateSelect.children('option').length > 1) return; // Already loaded

            const defaultCountryId = $('#checkout-country_id').val();
            const selectedStateId = {{ isset($selectedStateId) && $selectedStateId ? $selectedStateId : 'null' }};
            const selectedCityId = {{ isset($selectedCityId) && $selectedCityId ? $selectedCityId : 'null' }};

            if(defaultCountryId) {
                $.ajax({
                    url: '{{ route("website.locations.states") }}',
                    method: 'GET',
                    data: { country_id: defaultCountryId },
                    success: function(response) {
                        if (Array.isArray(response)) {
                            response.forEach(function(state) {
                                const selected = (selectedStateId && state.id == selectedStateId) ? 'selected' : '';
                                stateSelect.append(`<option value="${state.id}" ${selected}>${state.name}</option>`);
                            });

                            // If state is selected, trigger change to load cities
                            if (selectedStateId) {
                                stateSelect.trigger('change');

                                // After cities are loaded, select the city
                                setTimeout(function() {
                                    if (selectedCityId) {
                                        $('#checkout-city').val(selectedCityId).trigger('change');
                                    }
                                }, 500);
                            }
                        }
                    }
                });
            }
        }

        // Handle State Change
        $(document).on('change', '#checkout-state', function() {
            const stateId = $(this).val();
            const stateName = $(this).find('option:selected').text();
            $('#checkout-state_name').val(stateName);

            const citySelect = $('#checkout-city');
            citySelect.empty().append('<option value="">{{ __("website.select_city") ?? "Select City" }}</option>').prop('disabled', true);
            $('#checkout-city_name').val('');

            if (stateId) {
                const selectedCityId = {{ isset($selectedCityId) && $selectedCityId ? $selectedCityId : 'null' }};

                $.ajax({
                    url: '{{ route("website.locations.cities") }}',
                    method: 'GET',
                    data: { state_id: stateId },
                    success: function(response) {
                        if (Array.isArray(response)) {
                            response.forEach(function(city) {
                                const selected = (selectedCityId && city.id == selectedCityId) ? 'selected' : '';
                                citySelect.append(`<option value="${city.id}" ${selected}>${city.name}</option>`);
                            });
                            citySelect.prop('disabled', false);

                            // If city was selected, update the hidden field
                            if (selectedCityId && citySelect.val() == selectedCityId) {
                                const cityName = citySelect.find('option:selected').text();
                                $('#checkout-city_name').val(cityName);
                            }
                        }
                    }
                });
            }
        });

        $(document).on('change', '#checkout-city', function() {
            const selectedOption = $(this).find('option:selected');
            if (selectedOption.length && selectedOption.val()) {
                const cityName = selectedOption.text();
                $('#checkout-city_name').val(cityName);
            } else {
                $('#checkout-city_name').val('');
            }
        });

        // Initialize Map
        let map, marker, autocomplete;
        window.initCheckoutMap = function() {
            if (map) return; // Already initialized

            const defaultPos = { lat: 29.3759, lng: 47.9774 }; // Kuwait default
            const mapContainer = document.getElementById('checkout-address-map');

             if (!mapContainer || typeof google === 'undefined') {
                // If Google Maps is not loaded yet, wait and retry
                if (typeof google === 'undefined') {
                    setTimeout(window.initCheckoutMap, 500);
                }
                return;
            }

            map = new google.maps.Map(mapContainer, {
                center: defaultPos,
                zoom: 13,
                mapTypeControl: true,
                streetViewControl: false
            });

            marker = new google.maps.Marker({
                position: defaultPos,
                map: map,
                draggable: true,
                animation: google.maps.Animation.DROP
            });

            // Search Box
            const input = document.getElementById('checkout-address-search');
            if(input) {
                autocomplete = new google.maps.places.Autocomplete(input);
                autocomplete.bindTo('bounds', map);

                autocomplete.addListener('place_changed', function() {
                    const place = autocomplete.getPlace();
                    if (!place.geometry) return;

                    if (place.geometry.viewport) {
                        map.fitBounds(place.geometry.viewport);
                    } else {
                        map.setCenter(place.geometry.location);
                        map.setZoom(17);
                    }
                    marker.setPosition(place.geometry.location);
                    updateCoordinates(place.geometry.location);
                    fillAddressFromPlace(place);
                });
            }

            // Map Click
            map.addListener('click', function(e) {
                marker.setPosition(e.latLng);
                updateCoordinates(e.latLng);
            });

            // Marker Drag
            marker.addListener('dragend', function(e) {
                updateCoordinates(e.latLng);
            });

            // Locate Me
            $('#locate-me-checkout').on('click', function() {
                const locateBtn = $(this);
                const originalText = locateBtn.html();

                if (!navigator.geolocation) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("website.error") }}',
                        text: '{{ __("website.geolocation_not_supported") ?? "Geolocation is not supported by your browser" }}'
                    });
                    return;
                }

                locateBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __("website.locating") ?? "Locating..." }}');

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const pos = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        map.setCenter(pos);
                        marker.setPosition(pos);
                        updateCoordinates(pos);

                        // Reverse geocode
                        const geocoder = new google.maps.Geocoder();
                        geocoder.geocode({ location: pos }, function(results, status) {
                            if (status === 'OK' && results[0]) {
                                fillAddressFromPlace(results[0]);
                            }
                        });

                        locateBtn.prop('disabled', false).html(originalText);
                    },
                    function(error) {
                        locateBtn.prop('disabled', false).html(originalText);

                        let errorMessage = '{{ __("website.failed_to_get_location") ?? "Failed to get your location" }}';

                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage = '{{ __("website.location_permission_denied") ?? "Location access denied. Please enable location permissions in your browser settings." }}';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage = '{{ __("website.location_unavailable") ?? "Location information is unavailable. Please try again." }}';
                                break;
                            case error.TIMEOUT:
                                errorMessage = '{{ __("website.location_timeout") ?? "Location request timed out. Please try again." }}';
                                break;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("website.error") }}',
                            text: errorMessage
                        });
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 0
                    }
                );
            });
        }

        function updateCoordinates(latLng) {
            $('#checkout-latitude').val(latLng.lat());
            $('#checkout-longitude').val(latLng.lng());
        }

        function fillAddressFromPlace(place) {
             // Basic implementation
        }


        // Form Submit
        $(document).on('submit', '#add-address-form', function(e) {
            e.preventDefault();

            const form = $(this);
            const btn = $('#save-address-btn');
            const originalText = btn.text();

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                beforeSend: function() {
                    btn.prop('disabled', true).text('{{ __("website.saving") }}...');
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback').remove();
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("website.success") }}',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("website.error") }}',
                            text: '{{ __("website.something_went_wrong") }}'
                        });
                    }
                    btn.prop('disabled', false).text(originalText);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text(originalText);

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach(function(key) {
                            const input = form.find('[name="' + key + '"]');
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback">' + errors[key][0] + '</div>');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("website.error") }}',
                            text: xhr.responseJSON.message || '{{ __("website.something_went_wrong") }}'
                        });
                    }
                }
            });
        });
    });
    </script>

    <!-- Google Maps API -->
    @if(config('services.google_maps_api_key'))
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps_api_key') }}&libraries=places&callback=initCheckoutMap" async defer></script>
    @else
    <script>
        console.error('Google Maps API key is not configured');
    </script>
    @endif


    <script>
        // Radio buttons bridge for address type in checkout
        $(document).on('change', '#addAddressModal input[name="title"]', function() {
            $('#checkout_address_title').val($(this).val()).trigger('change');
        });

        // Sync UI on modal show
        $(document).on('shown.bs.modal', '#addAddressModal', function() {
            const currentVal = $('#checkout_address_title').val();
            if (currentVal) {
                $(`#addAddressModal input[name="title"][value="${currentVal}"]`).prop('checked', true);
            }
        });
    </script>
@endsection

