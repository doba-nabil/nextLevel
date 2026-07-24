<div class="proCont_wrapper">
    <!-- VIP Points Card -->
    <div class="vip-points-card mb-5">
        <div class="vip-points-header">
            <div>
                <div class="vip-points-value">
                    {{ auth()->user()->points ?? 0 }}
                    <small>points</small>
                </div>
                <div class="vip-target-info mt-2">
                    <i class="fa-solid fa-fire-flame-curved me-1 text-warning"></i>
                    {{ __('website.almost_reached_target') ?? 'Hurry up, you almost reach to your target' }}
                </div>
                @php
                    $pointsPerKd = (float) \App\Models\Setting::getValue('points_per_kd', null, 100);
                    $minimumPointsToConvert = (int) \App\Models\Setting::getValue('minimum_points_to_convert', null, 100);
                    $userPoints = auth()->user()->points ?? 0;
                    $canConvert = $userPoints >= $minimumPointsToConvert && $pointsPerKd > 0;
                    $convertedAmount = $canConvert ? ($userPoints / $pointsPerKd) : 0;
                @endphp
                @if($canConvert)
                    <div class="mt-3">
                        <button type="button" class="btn btn-dark px-4 py-2 convert-points-btn" data-points="{{ $userPoints }}" data-amount="{{ number_format($convertedAmount, 3) }}">
                            <i class="fa-solid fa-exchange-alt me-2"></i>
                            {{ __('website.convert_points_to_wallet') }} ({{ number_format($convertedAmount, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }})
                        </button>
                    </div>
                @else
                    <div class="mt-3">
                        <button type="button" class="btn btn-secondary px-4 py-2" disabled>
                            <i class="fa-solid fa-exchange-alt me-2"></i>
                            {{ __('website.convert_points_to_wallet') }}
                        </button>
                        <small class="d-block text-muted mt-1">
                            {{ __('website.minimum_points_required') }}: {{ $minimumPointsToConvert }} {{ __('website.points') }}
                        </small>
                    </div>
                @endif
            </div>
        </div>

        @php
            $currentPoints = auth()->user()->points ?? 0;
            $progressPercentage = min(($currentPoints / 1000) * 100, 100);
        @endphp

        <div class="vip-progress-container">
            <div class="vip-progress-bar">
                <div class="vip-progress-fill" style="width: {{ $progressPercentage }}%">
                    <img src="{{ asset('website/assets/img/fav.png') }}" alt="" class="vip-progress-icon">
                </div>
            </div>
            <div class="vip-rule-labels">
                <span>0</span>
                <span>1000</span>
                <span>3000</span>
                <span>6000</span>
                <span>10,000</span>
            </div>
        </div>

        <!-- Ruler Decorative Image (Optional, kept but styled better) -->
        <div class="mt-3">
             <img src="{{ asset('website') }}/assets/img/ruler.png" alt="" class="w-100" style="height: 10px; object-fit: cover;">
        </div>
    </div>

    <!-- Vouchers Section -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="profile-section-title mb-0">
            <i class="fa-solid fa-ticket gold-icon"></i>
            {{ __('website.your_vouchers') }}
        </h3>
    </div>

    <div class="row vouchers_row mb-5">
        @if(isset($coupons) && $coupons->count() > 0)
            @foreach($coupons as $coupon)
                <div class="col-12 col-xl-6 mb-4">
                    <div class="premium-ticket">
                        <div class="ticket-left">
                            <div class="ticket-discount">
                                @if($coupon->type === 'percent')
                                    {{ $coupon->value }}%
                                @else
                                    {{ number_format($coupon->value, 2) }} <small class="d-inline">{{ __('website.currency') }}</small>
                                @endif
                                <small>{{ __('website.discount') }}</small>
                            </div>
                            <div class="ticket-code-wrap mt-3">
                                <span class="code-label">{{ __('website.code') }}:</span>
                                <span class="code-value">{{ $coupon->code }}</span>
                            </div>
                        </div>

                        <div class="ticket-right">
                            <div class="ticket-meta">
                                <div class="meta-item">
                                    <i class="fa-regular fa-calendar-check me-2"></i>
                                    <span>
                                        @if($coupon->expire_at)
                                            {{ __('website.expiry') }}: {{ \Carbon\Carbon::parse($coupon->expire_at)->format('d M, Y') }}
                                        @else
                                            {{ __('website.no_expiry') }}
                                        @endif
                                    </span>
                                </div>
                                @if($coupon->min_order_price)
                                    <div class="meta-item mt-2">
                                        <i class="fa-solid fa-cart-shopping me-2"></i>
                                        <span>Min: {{ number_format($coupon->min_order_price, 2) }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="ticket-footer mt-3">
                                <small class="text-muted">{{ __('website.terms_and_conditions_apply') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12 text-center py-4 bg-light-soft rounded-4 border">
                <div class="opacity-50 mb-3">
                    <i class="fa-solid fa-ghost fa-3x"></i>
                </div>
                <h5 class="fw-bold text-muted">{{ __('website.no_vouchers_available') }}</h5>
                <p class="text-muted small">Check back later for exclusive rewards!</p>
            </div>
        @endif
    </div>

    <!-- Account Info Section -->
    <h3 class="profile-section-title mb-4">
        <i class="fa-solid fa-user-gear gold-icon"></i>
        {{ __('website.account_info') ?? 'Account Info' }}
    </h3>

    <div class="account-info-grid">
        <!-- Full Name -->
        <div class="info-item-card accountInfo_cardN" data-field="name">
            <div class="info-content">
                <div class="info-label">{{ __('website.full_name') }}</div>
                <div class="info-value-wrap">
                    <div class="info-value FName_des">{{ auth()->user()->name ?? __('website.not_set') }}</div>
                    <input type="text" name="name" class="premium-input hiddProf_input" value="{{ auth()->user()->name ?? '' }}" placeholder="{{ __('website.enter_your_full_name') }}" style="display: none;">
                    <div class="edit-controls mt-3 justify-content-end" style="display: none;">
                        <button type="button" class="btn btn-sm btn-light-danger closePro_bttn me-2">
                             <i class="fa-solid fa-times"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-dark px-3 savePro_bttn">
                             {{ __('website.save') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="info-actions">
                <button type="button" class="edit-circle-btn edit_bttn EDitProfile_bttn">
                    <i class="fa-solid fa-pen"></i>
                </button>
            </div>
        </div>

        <!-- Email -->
        <div class="info-item-card accountInfo_cardN" data-field="email">
            <div class="info-content">
                <div class="info-label">{{ __('website.email') }}</div>
                <div class="info-value-wrap">
                    <div class="info-value FName_des">{{ auth()->user()->email ?? __('website.not_set') }}</div>
                    <input type="email" name="email" class="premium-input hiddProf_input" value="{{ auth()->user()->email ?? '' }}" placeholder="{{ __('website.enter_your_email') }}" style="display: none;">
                    <div class="edit-controls mt-3 justify-content-end" style="display: none;">
                        <button type="button" class="btn btn-sm btn-light-danger closePro_bttn me-2">
                             <i class="fa-solid fa-times"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-dark px-3 savePro_bttn">
                             {{ __('website.save') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="info-actions">
                <button type="button" class="edit-circle-btn edit_bttn EDitProfile_bttn">
                    <i class="fa-solid fa-pen"></i>
                </button>
            </div>
        </div>

        <!-- Password -->
        <div class="info-item-card accountInfo_cardN" data-field="password">
            <div class="info-content w-100">
                <div class="info-label">{{ __('website.password') }}</div>
                <div class="info-value-wrap">
                    <div class="info-value FName_des">********************</div>
                    <div class="password-inputs hiddProf_input mt-2" style="display: none;">
                        <input type="password" name="current_password" class="premium-input mb-2 w-100" placeholder="{{ __('website.current_password') }}">
                        <input type="password" name="new_password" class="premium-input w-100" placeholder="{{ __('website.new_password') }}">
                    </div>
                    <div class="edit-controls mt-3 justify-content-end" style="display: none;">
                        <button type="button" class="btn btn-sm btn-light-danger closePro_bttn me-2">
                             <i class="fa-solid fa-times"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-dark px-3 savePro_bttn">
                             {{ __('website.save') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="info-actions">
                <button type="button" class="edit-circle-btn edit_bttn EDitProfile_bttn">
                    <i class="fa-solid fa-pen"></i>
                </button>
            </div>
        </div>

        <!-- Phone -->
        <div class="info-item-card accountInfo_cardN" data-field="phone">
            <div class="info-content">
                <div class="info-label">{{ __('website.mobile_number') }}</div>
                <div class="info-value-wrap">
                    <div class="info-value FName_des">
                        @if(auth()->user()->country && auth()->user()->phone)
                            <span class="country-pill">{{ auth()->user()->country->phone_code }}</span> {{ auth()->user()->phone }}
                        @elseif(auth()->user()->phone)
                            {{ auth()->user()->phone }}
                        @else
                            {{ __('website.not_set') }}
                        @endif
                    </div>
                    <div class="hiddProf_input mt-2" style="display: none;">
                        <div class="input-group">
                            @php
                                $countries = \App\Models\Location::where('type', 'country')->where('active', true)->orderBy('id')->get();
                                $firstCountry = $countries->first();
                                $selectedCountryId = auth()->user()->country_id ?: ($firstCountry ? $firstCountry->id : '');
                            @endphp
                            <select name="country_id" id="phone_country_id" class="premium-input select-country-inline">
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" data-phone-code="{{ $country->phone_code }}" {{ $selectedCountryId == $country->id ? 'selected' : '' }}>
                                        {{ $country->phone_code }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="tel" name="phone" id="phone" class="premium-input flex-grow-1" value="{{ auth()->user()->phone ?? '' }}" placeholder="99999999" maxlength="8">
                        </div>
                    </div>
                    <div class="edit-controls mt-3 justify-content-end" style="display: none;">
                        <button type="button" class="btn btn-sm btn-light-danger closePro_bttn me-2">
                             <i class="fa-solid fa-times"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-dark px-3 savePro_bttn">
                             {{ __('website.save') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="info-actions">
                <button type="button" class="edit-circle-btn edit_bttn EDitProfile_bttn">
                    <i class="fa-solid fa-pen"></i>
                </button>
            </div>
        </div>
    </div>
</div>
