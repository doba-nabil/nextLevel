<div class="proCont_wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h3 class="profile-section-title mb-0">
            <i class="fa-solid fa-map-location-dot gold-icon"></i>
            {{ __('website.my_addresses') }}
        </h3>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-dark premium-modal-btn px-4" id="add-address-btn">
                <i class="fa fa-plus me-1"></i> {{ __('website.add_address') }}
            </button>
            <button type="button" class="btn btn-outline-dark premium-modal-btn px-4 bg-white" id="view-addresses-map-btn">
                <i class="fa fa-map-marker-alt me-1"></i> {{ __('website.view_on_map') ?? 'Map' }}
            </button>
        </div>
    </div>

    <div id="addresses-list">
        @if(isset($addresses) && $addresses->count() > 0)
            <div class="row g-4">
                @foreach($addresses as $address)
                    <div class="col-12">
                        <div class="profile-content-card address-item-card {{ $address->is_main ? 'is-main' : '' }}" data-address-id="{{ $address->id }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex gap-3">
                                    <div class="address-radio-wrapper">
                                        <input type="radio" name="main_address" value="{{ $address->id }}"
                                               class="address-radio-profile"
                                               {{ $address->is_main ? 'checked' : '' }}
                                               data-address-id="{{ $address->id }}">
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <h5 class="address-title mb-0">
                                                @if($address->title == 'home') {{ __('website.address_title_home') }}
                                                @elseif($address->title == 'work') {{ __('website.address_title_work') }}
                                                @elseif($address->title == 'other') {{ __('website.address_title_other') }}
                                                @elseif($address->title) {{ $address->title }}
                                                @else {{ __('website.address') }} #{{ $address->id }}
                                                @endif
                                            </h5>
                                            @if($address->is_main)
                                                <span class="main-badge">
                                                    <i class="fa fa-star"></i> {{ __('website.main_address') }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="address-text">
                                            <i class="fa-solid fa-location-dot me-1 gold-icon"></i>
                                            {{ $address->full_address }}
                                        </div>
                                        @if($address->state || $address->city)
                                            <div class="address-city-text mt-1">
                                                {{ $address->state }}{{ $address->state && $address->city ? ', ' : '' }}{{ $address->city }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="address-actions dropdown">
                                    <button class="btn-dots" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-4">
                                        <li>
                                            <a class="dropdown-item edit-address-btn py-2 px-3" href="javascript:void(0)" data-address-id="{{ $address->id }}">
                                                <i class="fa-regular fa-pen-to-square me-2 text-primary"></i> {{ __('website.edit') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider opacity-50"></li>
                                        <li>
                                            <a class="dropdown-item delete-address-btn py-2 px-3 text-danger" href="javascript:void(0)" data-address-id="{{ $address->id }}">
                                                <i class="fa-regular fa-trash-can me-2"></i> {{ __('website.delete') }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="profile-content-card text-center py-5">
                <div class="mb-3">
                    <i class="fa-solid fa-map-pin fa-3x text-muted opacity-25"></i>
                </div>
                <p class="text-muted mb-4">{{ __('website.no_addresses_found') }}</p>
                <button class="btn btn-warning bg-gold border-0 premium-modal-btn px-4" id="add-address-btn-empty">
                    {{ __('website.add_address') }}
                </button>
            </div>
        @endif
    </div>
</div>


