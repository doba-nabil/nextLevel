<div class="DPick_formDIV">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <label class="login_label m-0">{{ __('website.delivery_address') }}</label>
        <button type="button" class="btn btn-dark premium-modal-btn btn-sm" id="add-address-btn">
            <i class="fa fa-plus me-1"></i> {{ __('website.add_new_address') }}
        </button>
    </div>

    <div id="checkout-addresses-list">
        @if($addresses->count() > 0)
            <div class="row">
                @php
                    // Find first available address
                    $firstAvailableAddressId = null;
                    foreach($addresses as $addr) {
                        $isAvail = is_array($addr) ? ($addr['is_available'] ?? false) : ($addr->is_available ?? true);
                        if ($isAvail) {
                            $firstAvailableAddressId = is_array($addr) ? $addr['id'] : $addr->id;
                            break;
                        }
                    }
                @endphp
                @foreach($addresses as $address)
                    @php
                        $isAvailable = is_array($address) ? ($address['is_available'] ?? false) : ($address->is_available ?? true);
                        $addressId = is_array($address) ? $address['id'] : $address->id;
                        $addressTitle = is_array($address) ? ($address['title'] ?? '') : ($address->title ?? '');
                        $addressFullAddress = is_array($address) ? ($address['full_address'] ?? '') : ($address->full_address ?? '');
                        $isMain = is_array($address) ? ($address['is_main'] ?? false) : ($address->is_main ?? false);
                        $stateName = is_array($address) ? ($address['state_name'] ?? '') : ($address->state ?? '');
                        $cityName = is_array($address) ? ($address['city_name'] ?? '') : ($address->city ?? '');
                        $shouldBeChecked = ($addressId === $firstAvailableAddressId && $isAvailable);
                    @endphp
                    <div class="col-12 mb-3">
                        <div class="address_card_checkout {{ $shouldBeChecked ? 'address-selected' : '' }} {{ !$isAvailable ? 'opacity-50' : '' }}" 
                             style="{{ !$isAvailable ? 'cursor: not-allowed; background-color: #f8f8f8 !important;' : 'cursor: pointer;' }}"
                             onclick="{{ $isAvailable ? "document.getElementById('address_$addressId').click()" : "" }}">
                            <div class="form-check d-flex align-items-start p-0">
                                <input class="form-check-input address-radio d-none" 
                                       type="radio" 
                                       name="address_id" 
                                       id="address_{{ $addressId }}" 
                                       value="{{ $addressId }}" 
                                       data-available="{{ $isAvailable ? '1' : '0' }}"
                                       data-is-main="{{ $isMain ? '1' : '0' }}"
                                       {{ $shouldBeChecked ? 'checked' : '' }}
                                       {{ !$isAvailable ? 'disabled' : '' }}>
                                <div class="w-100 ps-0">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="fa-solid {{ $shouldBeChecked ? 'fa-circle-check text-dark' : 'fa-circle-dot text-muted' }} me-2 fs-5"></i>
                                            <span class="fw-bold text-dark">
                                                @if($addressTitle == 'home') {{ __('website.address_title_home') }}
                                                @elseif($addressTitle == 'work') {{ __('website.address_title_work') }}
                                                @elseif($addressTitle == 'other') {{ __('website.address_title_other') }}
                                                @else {{ $addressTitle ?: __('website.address') }}
                                                @endif
                                            </span>
                                            @if($isMain)
                                                <span class="badge bg-main-address ms-2">
                                                    {{ __('website.main') }}
                                                </span>
                                            @endif
                                        </div>
                                        @if(!$isAvailable)
                                            <span class="badge bg-light text-danger border border-danger-subtle rounded-0 px-2 py-1" style="font-size: 0.65rem;">
                                                <i class="fa fa-exclamation-triangle me-1"></i>
                                                {{ __('website.address_out_of_service_area') ?? 'Out of Area' }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="address-content ps-4">
                                        <p class="mb-1 text-muted" style="font-size: 0.9rem; line-height: 1.4;">
                                            {{ $addressFullAddress }}
                                        </p>
                                        @if($stateName || $cityName)
                                            <p class="mb-0 text-muted small fw-bold">
                                                @if($stateName && $cityName)
                                                    {{ $stateName }}, {{ $cityName }}
                                                @elseif($stateName)
                                                    {{ $stateName }}
                                                @elseif($cityName)
                                                    {{ $cityName }}
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5 border bg-light">
                <i class="fa-solid fa-map-location-dot fa-3x text-muted mb-3 opacity-25"></i>
                <p class="text-muted mb-4">{{ __('website.no_addresses_found') }}</p>
                <button type="button" class="btn btn-dark premium-modal-btn" id="add-address-btn-empty">
                    <i class="fa fa-plus me-1"></i> {{ __('website.add_new_address') }}
                </button>
            </div>
            <input type="hidden" name="address_id" required> 
        @endif
        @error('address_id')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
</div>

<!-- Add Address Modal -->



