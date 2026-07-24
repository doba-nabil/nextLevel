<!-- Address Modals Collection - Redesigned for Premium Look -->

<!-- 1. Add/Edit Address Modal -->
<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title d-flex align-items-center gap-2" id="addressModalLabel">
                    <i class="fa-solid fa-map-location-dot text-warning"></i>
                    {{ __('website.add_address') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="address-form">
                <div class="modal-body px-4 pt-3">
                    <input type="hidden" id="address_id" name="address_id">
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('website.address_title') }}</label>
                            <div class="address-type-selector d-flex gap-2">
                                <input type="radio" class="btn-check" name="title" id="type_home" value="home" checked>
                                <label class="btn btn-outline-light flex-fill type-label" for="type_home">
                                    <i class="fa-solid fa-house mb-1 d-block"></i>
                                    {{ __('website.address_title_home') }}
                                </label>

                                <input type="radio" class="btn-check" name="title" id="type_work" value="work">
                                <label class="btn btn-outline-light flex-fill type-label" for="type_work">
                                    <i class="fa-solid fa-briefcase mb-1 d-block"></i>
                                    {{ __('website.address_title_work') }}
                                </label>

                                <input type="radio" class="btn-check" name="title" id="type_other" value="other">
                                <label class="btn btn-outline-light flex-fill type-label" for="type_other">
                                    <i class="fa-solid fa-location-dot mb-1 d-block"></i>
                                    {{ __('website.address_title_other') }}
                                </label>
                            </div>
                            <select class="d-none" id="address_title" name="title_hidden">
                                <option value="home">home</option>
                                <option value="work">work</option>
                                <option value="other">other</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check custom-check pb-2 ms-md-3">
                                <input class="form-check-input" type="checkbox" id="is_main" name="is_main">
                                <label class="form-check-label fw-bold" for="is_main">
                                    {{ __('website.set_as_main_address') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4" style="display: none;">
                        <label for="address" class="form-label">{{ __('website.full_address') }}</label>
                        <textarea class="form-control premium-input" id="address" name="address" rows="2"></textarea>
                    </div>
                    
                    @php
                        $defaultCountry = \App\Models\Location::where('type', 'country')->where('active', true)->orderBy('id')->first();
                    @endphp
                    <input type="hidden" id="country" name="country" value="{{ $defaultCountry?->getTranslation('name', app()->getLocale()) ?? '' }}">
                    <input type="hidden" id="country_id" name="country_id" value="{{ $defaultCountry?->id ?? '' }}">
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="state" class="form-label">{{ __('website.state') }}</label>
                            <select class="form-select premium-input" id="state" name="state">
                                <option value="">{{ __('website.select_state') ?? 'Select State' }}</option>
                            </select>
                            <input type="hidden" id="state_name" name="state_name">
                        </div>
                        <div class="col-md-6">
                            <label for="city" class="form-label">{{ __('website.city') }}</label>
                            <select class="form-select premium-input" id="city" name="city" disabled>
                                <option value="">{{ __('website.select_city') ?? 'Select City' }}</option>
                            </select>
                            <input type="hidden" id="city_name" name="city_name">
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="block" class="form-label">{{ __('website.block') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control premium-input" id="block" name="block" required>
                        </div>
                        <div class="col-md-4">
                            <label for="street" class="form-label">{{ __('website.street') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control premium-input" id="street" name="street" required>
                        </div>
                        <div class="col-md-4">
                            <label for="avenue" class="form-label">{{ __('website.avenue') }}</label>
                            <input type="text" class="form-control premium-input" id="avenue" name="avenue">
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="building" class="form-label">{{ __('website.building') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control premium-input" id="building" name="building" required>
                        </div>
                        <div class="col-md-4">
                            <label for="floor" class="form-label">{{ __('website.floor') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control premium-input" id="floor" name="floor" required>
                        </div>
                        <div class="col-md-4">
                            <label for="apartment" class="form-label">{{ __('website.apartment') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control premium-input" id="apartment" name="apartment" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="additional_directions" class="form-label">{{ __('website.additional_directions') }}</label>
                        <textarea class="form-control premium-input" id="additional_directions" name="additional_directions" rows="2" placeholder="{{ __('website.additional_directions_placeholder') ?? 'Any extra details...' }}"></textarea>
                    </div>
                    
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                    
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label mb-0 fw-bold">{{ __('website.select_location_on_map') ?? 'Select Location on Map' }}</label>
                            <button type="button" id="locate-me-profile" class="btn btn-dark premium-modal-btn btn-sm">
                                <i class="fa-solid fa-location-crosshairs me-1"></i> {{ __('website.locate_me') ?? 'Locate Me' }}
                            </button>
                        </div>
                        <div id="profile-address-map-container" class="position-relative overflow-hidden border">
                            <input type="text" id="profile-address-search" class="form-control premium-input map-search-box" 
                                   placeholder="{{ __('website.search_location') ?? 'Search for a location...' }}" 
                                   autocomplete="new-password">
                            <div id="profile-address-map" style="width: 100%; height: 100%;"></div>
                        </div>
                    </div>
                    
                    <div id="address-form-error" class="alert alert-danger mt-3" style="display: none; border-radius: 12px;"></div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-light premium-modal-btn" data-bs-dismiss="modal">{{ __('website.cancel') }}</button>
                    <button type="submit" class="btn btn-warning premium-modal-btn bg-gold text-dark border-0" id="save-address-btn">{{ __('website.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. Addresses Map Modal -->
<div class="modal fade" id="addressesMapModal" tabindex="-1" aria-labelledby="addressesMapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title d-flex align-items-center gap-2" id="addressesMapModalLabel">
                    <i class="fa-solid fa-earth-americas text-warning"></i>
                    {{ __('website.my_addresses') }} - {{ __('website.view_on_map') ?? 'Map View' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-3">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="map-sidebar rounded-4 overflow-hidden border bg-light">
                            <div class="p-3 border-bottom bg-white">
                                <h6 class="mb-0 fw-bold">{{ __('website.select_address') }}</h6>
                            </div>
                            <div id="addresses-map-list" class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                                <!-- Addresses will be loaded here -->
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div id="addresses-map-container" class="rounded-4 overflow-hidden border shadow-sm" style="height: 550px;">
                            <div id="addresses-map" style="height: 100%; width: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4">
                <button type="button" class="btn btn-dark premium-modal-btn" data-bs-dismiss="modal">{{ __('website.close') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- 3. Address Selection Modal -->
@auth('web')
<div class="modal fade" id="profileAddressSelectionModal" tabindex="-1" aria-labelledby="profileAddressSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title d-flex align-items-center gap-2" id="profileAddressSelectionModalLabel">
                    <i class="fa-solid fa-list-check text-warning"></i>
                    {{ __('website.select_address') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-3">
                <div class="mb-4">
                    <h6 class="mb-3 fw-bold">{{ __('website.my_addresses') }}</h6>
                    <div id="profile-addresses-list" class="list-group rounded-4 overflow-hidden border">
                        <!-- Addresses will be loaded here -->
                    </div>
                    <div id="profile-no-addresses" class="text-center py-5 border rounded-4 bg-light mt-3" style="display: none;">
                        <i class="fa-solid fa-map-pin fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-3">{{ __('website.no_addresses_found') }}</p>
                        <button class="btn btn-warning bg-gold border-0 premium-modal-btn" onclick="event.preventDefault(); if (typeof resetAddressForm === 'function') { resetAddressForm(); } $('#addressModal').modal('show'); $('#profileAddressSelectionModal').modal('hide');">
                            <i class="fa fa-plus me-1"></i> {{ __('website.add_address') }}
                        </button>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __('website.select_location_on_map') ?? 'Select Location on Map' }}</label>
                    <div id="profile-address-selection-map-container" class="rounded-4 overflow-hidden border position-relative" style="height: 350px;">
                        <input type="text" id="profile-address-selection-search" class="form-control premium-input map-search-box" 
                               placeholder="{{ __('website.search_location') ?? 'Search for a location...' }}" 
                               autocomplete="new-password">
                        <div id="profile-address-selection-map" style="height: 100%; width: 100%;"></div>
                    </div>
                </div>
                
                <div id="profile-selected-address" class="alert alert-warning border-0 bg-light-warning rounded-4 p-3" style="display: none;">
                    <div class="d-flex gap-3 align-items-center">
                        <i class="fa-solid fa-location-dot text-warning fa-lg"></i>
                        <div>
                            <strong class="d-block mb-1 text-dark">{{ __('website.selected_address') ?? 'Selected Address' }}</strong>
                            <span id="profile-address-display" class="text-muted small"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4">
                <button type="button" class="btn btn-light premium-modal-btn" data-bs-dismiss="modal">{{ __('website.cancel') }}</button>
                <button type="button" class="btn btn-dark premium-modal-btn" id="profile-select-address-btn" disabled>{{ __('website.select') }}</button>
            </div>
        </div>
    </div>
</div>
@endauth


<script>
    // Radio buttons bridge for address type
    $(document).on('change', 'input[name="title"]', function() {
        $('#address_title').val($(this).val()).trigger('change');
    });

    // Sync UI on modal show
    $(document).on('shown.bs.modal', '#addressModal', function() {
        const currentVal = $('#address_title').val();
        if (currentVal) {
            $(`input[name="title"][value="${currentVal}"]`).prop('checked', true);
        }
    });
</script>
