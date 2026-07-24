@extends('dashboard.layout.master')
@section('title', __('admin.addresses') . ' - ' . $user->name)

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __('admin.addresses') . ' - ' . $user->name }}</h5>
                        <div>
                            <a href="{{ route('users.index') }}" class="btn btn-label-secondary">
                                <i class="icon-base ti tabler-arrow-left"></i> {{ __('admin.back') }}
                            </a>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                <i class="icon-base ti tabler-plus"></i> {{ __('admin.add_address') }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if($addresses->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{ __('admin.title') }}</th>
                                            <th>{{ __('admin.address') }}</th>
                                            <th>{{ __('admin.city') }}</th>
                                            <th>{{ __('admin.state') }}</th>
                                            <th>{{ __('admin.is_main') }}</th>
                                            <th>{{ __('admin.status') }}</th>
                                            <th>{{ __('admin.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($addresses as $address)
                                            <tr>
                                                <td>
                                                    <strong>{{ $address->title ?? __('admin.address') . ' #' . $address->id }}</strong>
                                                    @if($address->is_main)
                                                        <span class="badge bg-label-primary ms-2">{{ __('admin.main') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $address->address ?? '-' }}
                                                    @if($address->building)
                                                        <br><small class="text-muted">{{ __('admin.building') }}: {{ $address->building }}</small>
                                                    @endif
                                                    @if($address->floor)
                                                        <br><small class="text-muted">{{ __('admin.floor') }}: {{ $address->floor }}</small>
                                                    @endif
                                                    @if($address->apartment)
                                                        <br><small class="text-muted">{{ __('admin.apartment') }}: {{ $address->apartment }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $address->city ?? '-' }}</td>
                                                <td>{{ $address->state ?? '-' }}</td>
                                                <td>
                                                    @if($address->is_main)
                                                        <span class="badge bg-label-success">{{ __('admin.yes') }}</span>
                                                    @else
                                                        <span class="badge bg-label-secondary">{{ __('admin.no') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($address->active)
                                                        <span class="badge bg-label-success">{{ __('admin.active') }}</span>
                                                    @else
                                                        <span class="badge bg-label-secondary">{{ __('admin.deactive') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-default" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="icon-base ti tabler-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a href="javascript:void(0)" class="dropdown-item edit-address-btn" data-address-id="{{ $address->id }}">
                                                                    <i class="icon-base ti tabler-edit"></i> {{ __('admin.edit') }}
                                                                </a>
                                                            </li>
                                                            @if(!$address->is_main)
                                                                <li>
                                                                    <a href="javascript:void(0)" class="dropdown-item set-main-address-btn" data-address-id="{{ $address->id }}">
                                                                        <i class="icon-base ti tabler-star"></i> {{ __('admin.set_as_main') }}
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            <li>
                                                                <a href="javascript:void(0)" class="dropdown-item delete-address-btn" data-address-id="{{ $address->id }}">
                                                                    <i class="icon-base ti tabler-trash"></i> {{ __('admin.delete') }}
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <p class="mb-0">{{ __('admin.no_addresses_found') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Address Modal -->
    <div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('admin.add_address') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addAddressForm" method="POST" action="{{ route('users.addresses.store', $user->id) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.title') }} <span class="text-danger">*</span></label>
                                <select class="form-control" name="title" id="add_address_title" required>
                                    <option value="">{{ __('admin.select') ?? 'Select' }}</option>
                                    <option value="home" selected>{{ __('admin.address_title_home') ?? 'Home' }}</option>
                                    <option value="work">{{ __('admin.address_title_work') ?? 'Work' }}</option>
                                    <option value="other">{{ __('admin.address_title_other') ?? 'Other' }}</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_main" id="is_main" value="1">
                                    <label class="form-check-label" for="is_main">
                                        {{ __('admin.set_as_main') }}
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-12 mb-3" style="display: none;">
                                <label class="form-label">{{ __('admin.address') }}</label>
                                <textarea name="address" class="form-control" rows="3"></textarea>
                            </div>
                            
                            @php
                                $defaultCountry = \App\Models\Location::where('type', 'country')->where('active', true)->orderBy('id')->first();
                            @endphp
                            <input type="hidden" name="country" id="add_country" value="{{ $defaultCountry?->getTranslation('name', app()->getLocale()) ?? '' }}">
                            <input type="hidden" name="country_id" id="add_country_id" value="{{ $defaultCountry?->id ?? '' }}">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('admin.state') }}</label>
                                    <select class="form-control" id="add_state" name="state">
                                        <option value="">{{ __('admin.select_state') ?? 'Select State' }}</option>
                                    </select>
                                    <input type="hidden" id="add_state_name" name="state_name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('admin.city') }}</label>
                                    <select class="form-control" id="add_city" name="city" disabled>
                                        <option value="">{{ __('admin.select_city') ?? 'Select City' }}</option>
                                    </select>
                                    <input type="hidden" id="add_city_name" name="city_name">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3 mb-3 d-none">
                                    <label class="form-label">{{ __('admin.area') }}</label>
                                    <input type="text" name="area" id="add_area" class="form-control">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">{{ __('admin.block') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="block" id="add_block" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">{{ __('admin.street') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="street" id="add_street" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">{{ __('admin.avenue') }}</label>
                                    <input type="text" name="avenue" id="add_avenue" class="form-control">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">{{ __('admin.building') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="building" id="add_building" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">{{ __('admin.floor') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="floor" id="add_floor" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">{{ __('admin.apartment') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="apartment" id="add_apartment" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">{{ __('admin.additional_directions') }}</label>
                                <textarea name="additional_directions" id="add_additional_directions" class="form-control" rows="2"></textarea>
                            </div>
                            <!-- Hidden latitude and longitude inputs (populated by map) -->
                            <input type="hidden" name="latitude" id="add_latitude" value="">
                            <input type="hidden" name="longitude" id="add_longitude" value="">
                            
                            <!-- Google Maps for location selection -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">{{ __('admin.select_location_on_map') }}</label>
                                    <button type="button" id="locate-me-add-admin" class="btn btn-primary btn-sm">
                                        <i class="fa-solid fa-location-crosshairs me-1"></i> {{ __('admin.locate_me') ?? 'Locate Me' }}
                                    </button>
                                </div>
                                <div id="add-address-map-container" style="position: relative; width: 100%; height: 350px; border: 1px solid #ddd; border-radius: 8px;">
                                    <input type="text" id="add-address-location-search" class="form-control" 
                                           placeholder="{{ __('admin.search_location') ?? 'Search for a location...' }}" 
                                           autocomplete="new-password"
                                           style="position: absolute; top: 10px; left: 50%; transform: translateX(-50%); width: 90%; z-index: 10; height: 40px; padding: 0 12px; border: 2px solid #007bff; border-radius: 4px; background-color: white;">
                                    <div id="add-address-map" style="width: 100%; height: 100%; border-radius: 8px;"></div>
                                </div>
                                <small class="text-muted">{{ __('admin.type_to_search_location') ?? 'Type to search for a location or click on the map to set coordinates' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Address Modal -->
    <div class="modal fade" id="editAddressModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('admin.edit_address') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editAddressForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.title') }} <span class="text-danger">*</span></label>
                                <select class="form-control" name="title" id="edit_address_title" required>
                                    <option value="">{{ __('admin.select') ?? 'Select' }}</option>
                                    <option value="home">{{ __('admin.address_title_home') ?? 'Home' }}</option>
                                    <option value="work">{{ __('admin.address_title_work') ?? 'Work' }}</option>
                                    <option value="other">{{ __('admin.address_title_other') ?? 'Other' }}</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_main" id="edit_is_main" value="1">
                                    <label class="form-check-label" for="edit_is_main">
                                        {{ __('admin.set_as_main') }}
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-12 mb-3" style="display: none;">
                                <label class="form-label">{{ __('admin.address') }}</label>
                                <textarea name="address" class="form-control" rows="3"></textarea>
                            </div>
                            
                            @php
                                $defaultCountry = \App\Models\Location::where('type', 'country')->where('active', true)->orderBy('id')->first();
                            @endphp
                            <input type="hidden" name="country" id="edit_country" value="{{ $defaultCountry?->getTranslation('name', app()->getLocale()) ?? '' }}">
                            <input type="hidden" name="country_id" id="edit_country_id" value="{{ $defaultCountry?->id ?? '' }}">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('admin.state') }}</label>
                                    <select class="form-control" id="edit_state" name="state">
                                        <option value="">{{ __('admin.select_state') ?? 'Select State' }}</option>
                                    </select>
                                    <input type="hidden" id="edit_state_name" name="state_name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('admin.city') }}</label>
                                    <select class="form-control" id="edit_city" name="city" disabled>
                                        <option value="">{{ __('admin.select_city') ?? 'Select City' }}</option>
                                    </select>
                                    <input type="hidden" id="edit_city_name" name="city_name">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3 mb-3 d-none">
                                    <label class="form-label">{{ __('admin.area') }}</label>
                                    <input type="text" name="area" id="edit_area" class="form-control">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">{{ __('admin.block') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="block" id="edit_block" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">{{ __('admin.street') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="street" id="edit_street" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">{{ __('admin.avenue') }}</label>
                                    <input type="text" name="avenue" id="edit_avenue" class="form-control">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">{{ __('admin.building') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="building" id="edit_building" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">{{ __('admin.floor') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="floor" id="edit_floor" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">{{ __('admin.apartment') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="apartment" id="edit_apartment" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">{{ __('admin.additional_directions') }}</label>
                                <textarea name="additional_directions" id="edit_additional_directions" class="form-control" rows="2"></textarea>
                            </div>
                            
                            <!-- Hidden latitude and longitude inputs (populated by map) -->
                            <input type="hidden" name="latitude" id="edit_latitude" value="">
                            <input type="hidden" name="longitude" id="edit_longitude" value="">
                            
                            <!-- Google Maps for location selection -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">{{ __('admin.select_location_on_map') }}</label>
                                    <button type="button" id="locate-me-edit-admin" class="btn btn-primary btn-sm">
                                        <i class="fa-solid fa-location-crosshairs me-1"></i> {{ __('admin.locate_me') ?? 'Locate Me' }}
                                    </button>
                                </div>
                                <div id="edit-address-map-container" style="position: relative; width: 100%; height: 350px; border: 1px solid #ddd; border-radius: 8px;">
                                    <input type="text" id="edit-address-location-search" class="form-control" 
                                           placeholder="{{ __('admin.search_location') ?? 'Search for a location...' }}" 
                                           autocomplete="new-password"
                                           style="position: absolute; top: 10px; left: 50%; transform: translateX(-50%); width: 90%; z-index: 10; height: 40px; padding: 0 12px; border: 2px solid #007bff; border-radius: 4px; background-color: white;">
                                    <div id="edit-address-map" style="width: 100%; height: 100%; border-radius: 8px;"></div>
                                </div>
                                <small class="text-muted">{{ __('admin.type_to_search_location') ?? 'Type to search for a location or click on the map to set coordinates' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('admin.update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('dashboard-footer')
    <script src="{{ asset('dashboard') }}/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
    <script>
        'use strict';
        $(document).ready(function() {
            // Handle add address form submission
            $('#addAddressForm').on('submit', function(e) {
                e.preventDefault();
                
                const form = $(this);
                const formData = form.serialize();
                const url = form.attr('action');
                
                // Clear previous errors
                $('.text-danger').remove();
                $('.is-invalid').removeClass('is-invalid');
                
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("admin.success") }}',
                            text: '{{ __("admin.add_success") }}',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            // Validation errors
                            const errors = xhr.responseJSON.errors;
                            let errorHtml = '<div class="alert alert-danger"><ul class="mb-0">';
                            
                            $.each(errors, function(key, value) {
                                const field = form.find('[name="' + key + '"]');
                                field.addClass('is-invalid');
                                field.after('<div class="invalid-feedback d-block">' + value[0] + '</div>');
                                errorHtml += '<li>' + value[0] + '</li>';
                            });
                            
                            errorHtml += '</ul></div>';
                            form.find('.modal-body').prepend(errorHtml);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __("admin.error") }}',
                                text: xhr.responseJSON?.message || '{{ __("admin.error_occurred") }}'
                            });
                        }
                    }
                });
            });
            
            // Handle edit address form submission
            $('#editAddressForm').on('submit', function(e) {
                e.preventDefault();
                
                const form = $(this);
                const formData = form.serialize();
                const url = form.attr('action');
                
                // Clear previous errors
                $('.text-danger').remove();
                $('.is-invalid').removeClass('is-invalid');
                
                $.ajax({
                    url: url,
                    method: 'PUT',
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("admin.success") }}',
                            text: '{{ __("admin.update_success") }}',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            // Validation errors
                            const errors = xhr.responseJSON.errors;
                            let errorHtml = '<div class="alert alert-danger"><ul class="mb-0">';
                            
                            $.each(errors, function(key, value) {
                                const field = form.find('[name="' + key + '"]');
                                field.addClass('is-invalid');
                                field.after('<div class="invalid-feedback d-block">' + value[0] + '</div>');
                                errorHtml += '<li>' + value[0] + '</li>';
                            });
                            
                            errorHtml += '</ul></div>';
                            form.find('.modal-body').prepend(errorHtml);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __("admin.error") }}',
                                text: xhr.responseJSON?.message || '{{ __("admin.error_occurred") }}'
                            });
                        }
                    }
                });
            });
            
            // Edit address
            $(document).on('click', '.edit-address-btn', function() {
                const addressId = $(this).data('address-id');
                $.ajax({
                    url: '{{ route("users.addresses.show", ["user" => $user->id, "address" => ":id"]) }}'.replace(':id', addressId),
                    method: 'GET',
                    success: function(response) {
                        const address = response.address;
                        $('#editAddressForm').attr('action', '{{ route("users.addresses.update", ["user" => $user->id, "address" => "ADDRESS_ID"]) }}'.replace('ADDRESS_ID', addressId));
                        
                        // Set title select
                        $('#edit_address_title').val(address.title || 'home');
                        
                        // Set block, street, avenue
                        $('#edit_block').val(address.block || '');
                        $('#edit_street').val(address.street || '');
                        $('#edit_avenue').val(address.avenue || '');
                        $('#edit_area').val(address.area || '');
                        $('#edit_building').val(address.building || '');
                        $('#edit_floor').val(address.floor || '');
                        $('#edit_apartment').val(address.apartment || '');
                        $('#edit_additional_directions').val(address.additional_directions || '');
                        $('#edit_latitude').val(address.latitude || '');
                        $('#edit_longitude').val(address.longitude || '');
                        $('#edit_is_main').prop('checked', address.is_main == 1);
                        
                        // Load states and cities for edit
                        loadEditAdminStates(function() {
                            if (address.state) {
                                // Try to find state by name
                                const stateOption = $('#edit_state option').filter(function() {
                                    return $(this).text().toLowerCase().includes(address.state.toLowerCase()) ||
                                           address.state.toLowerCase().includes($(this).text().toLowerCase());
                                }).first();
                                if (stateOption.length > 0) {
                                    $('#edit_state').val(stateOption.val()).trigger('change');
                                    $('#edit_state_name').val(stateOption.text());
                                    setTimeout(function() {
                                        loadEditAdminCities(stateOption.val(), function() {
                                            if (address.city) {
                                                const cityOption = $('#edit_city option').filter(function() {
                                                    return $(this).text().toLowerCase().includes(address.city.toLowerCase()) ||
                                                           address.city.toLowerCase().includes($(this).text().toLowerCase());
                                                }).first();
                                                if (cityOption.length > 0) {
                                                    $('#edit_city').val(cityOption.val());
                                                    $('#edit_city_name').val(cityOption.text());
                                                }
                                            }
                                        });
                                    }, 300);
                                } else {
                                    // If state not found, set as text input
                                    $('#edit_state').after('<input type="hidden" id="edit_state_name_fallback" name="state_name" value="' + (address.state || '') + '">');
                                }
                            }
                        });
                        
                        // Show modal first
                        const editModalElement = document.getElementById('editAddressModal');
                        if (editModalElement) {
                            let editModal = bootstrap.Modal.getInstance(editModalElement);
                            if (!editModal) {
                                editModal = new bootstrap.Modal(editModalElement);
                            }
                            editModal.show();
                            
                            // Initialize map after modal is shown
                            setTimeout(function() {
                                if (typeof initEditAddressMap === 'function') {
                                    initEditAddressMap(address.latitude || 29.3759, address.longitude || 47.9774);
                                }
                            }, 300);
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("admin.error") }}',
                            text: '{{ __("admin.error_occurred") }}'
                        });
                    }
                });
            });

            // Set as main address
            $(document).on('click', '.set-main-address-btn', function() {
                const addressId = $(this).data('address-id');
                Swal.fire({
                    title: '{{ __("admin.sure") }}',
                    text: '{{ __("admin.set_as_main_address_confirmation") }}',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '{{ __("admin.yes") }}',
                    cancelButtonText: '{{ __("admin.cancel") }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route("users.addresses.set-main", ["user" => $user->id, "address" => ":id"]) }}'.replace(':id', addressId),
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function() {
                                Swal.fire({
                                    icon: 'success',
                                    title: '{{ __("admin.success") }}',
                                    text: '{{ __("admin.update_success") }}',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __("admin.error") }}',
                                    text: '{{ __("admin.error_occurred") }}'
                                });
                            }
                        });
                    }
                });
            });

            // Delete address
            $(document).on('click', '.delete-address-btn', function() {
                const addressId = $(this).data('address-id');
                Swal.fire({
                    title: '{{ __("admin.sure") }}',
                    text: '{{ __("admin.cant") }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ __("admin.yes_sure") }}',
                    cancelButtonText: '{{ __("admin.cancel") }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route("users.addresses.destroy", ["user" => $user->id, "address" => ":id"]) }}'.replace(':id', addressId),
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function() {
                                Swal.fire({
                                    icon: 'success',
                                    title: '{{ __("admin.delete_success") }}',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __("admin.error") }}',
                                    text: '{{ __("admin.delete_error") }}'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
    
    <!-- Google Maps API -->
    @if(config('services.google_maps_api_key'))
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps_api_key') }}&libraries=places" async defer></script>
    @else
    <script>
        console.error('Google Maps API key is not configured');
    </script>
    @endif
    
    <script>
        let addAddressMap;
        let addAddressMarker;
        let addAddressAutocomplete;
        let editAddressMap;
        let editAddressMarker;
        let editAddressAutocomplete;
        
        // Initialize map for Add Address Modal
        function initAddAddressMap() {
            const mapDiv = document.getElementById('add-address-map');
            if (!mapDiv) return;
            
            // Default location (Kuwait)
            const defaultLat = 29.3759;
            const defaultLng = 47.9774;
            
            addAddressMap = new google.maps.Map(mapDiv, {
                center: { lat: defaultLat, lng: defaultLng },
                zoom: 13,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true
            });
            
            // Add marker
            addAddressMarker = new google.maps.Marker({
                map: addAddressMap,
                draggable: true,
                position: { lat: defaultLat, lng: defaultLng }
            });
            
            // Initialize Autocomplete for search
            const searchInput = document.getElementById('add-address-location-search');
            if (searchInput && typeof google !== 'undefined' && google.maps && google.maps.places) {
                try {
                    addAddressAutocomplete = new google.maps.places.Autocomplete(searchInput, {
                        types: ['geocode', 'establishment'],
                        fields: ['geometry', 'formatted_address', 'address_components', 'name']
                    });
                    
                    // Bias Autocomplete results towards current map's viewport
                    addAddressMap.addListener('bounds_changed', function() {
                        const bounds = addAddressMap.getBounds();
                        if (bounds && addAddressAutocomplete) {
                            addAddressAutocomplete.setBounds(bounds);
                        }
                    });
                    
                    // When a place is selected from autocomplete
                    addAddressAutocomplete.addListener('place_changed', function() {
                        const place = addAddressAutocomplete.getPlace();
                        
                        if (!place.geometry || !place.geometry.location) {
                            return;
                        }
                        
                        // Update marker position
                        addAddressMarker.setPosition(place.geometry.location);
                        
                        // Center map on selected place
                        if (place.geometry.viewport) {
                            addAddressMap.fitBounds(place.geometry.viewport);
                        } else {
                            addAddressMap.setCenter(place.geometry.location);
                            addAddressMap.setZoom(15);
                        }
                        
                        // Update coordinates
                        updateAddAddressCoordinates(place.geometry.location.lat(), place.geometry.location.lng());
                    });
                } catch (error) {
                    console.error('Error initializing Autocomplete:', error);
                }
            }
            
            // Update coordinates when marker is dragged
            addAddressMarker.addListener('dragend', function() {
                const position = addAddressMarker.getPosition();
                updateAddAddressCoordinates(position.lat(), position.lng());
            });
            
            // Update coordinates when map is clicked
            addAddressMap.addListener('click', function(event) {
                const lat = event.latLng.lat();
                const lng = event.latLng.lng();
                addAddressMarker.setPosition({ lat: lat, lng: lng });
                updateAddAddressCoordinates(lat, lng);
            });
            
            // Set default values
            updateAddAddressCoordinates(defaultLat, defaultLng);
        }
        
        function updateAddAddressCoordinates(lat, lng) {
            const latInput = document.getElementById('add_latitude');
            const lngInput = document.getElementById('add_longitude');
            if (latInput) latInput.value = lat.toFixed(8);
            if (lngInput) lngInput.value = lng.toFixed(8);
        }
        
        // Initialize map for Edit Address Modal
        function initEditAddressMap(lat = 29.3759, lng = 47.9774) {
            const mapDiv = document.getElementById('edit-address-map');
            if (!mapDiv) return;
            
            // Destroy existing map if any
            if (editAddressMap) {
                editAddressMap = null;
                editAddressMarker = null;
            }
            
            editAddressMap = new google.maps.Map(mapDiv, {
                center: { lat: lat, lng: lng },
                zoom: 13,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true
            });
            
            // Add marker
            editAddressMarker = new google.maps.Marker({
                map: editAddressMap,
                draggable: true,
                position: { lat: lat, lng: lng }
            });
            
            // Initialize Autocomplete for search
            const searchInput = document.getElementById('edit-address-location-search');
            if (searchInput && typeof google !== 'undefined' && google.maps && google.maps.places) {
                try {
                    editAddressAutocomplete = new google.maps.places.Autocomplete(searchInput, {
                        types: ['geocode', 'establishment'],
                        fields: ['geometry', 'formatted_address', 'address_components', 'name']
                    });
                    
                    // Bias Autocomplete results towards current map's viewport
                    editAddressMap.addListener('bounds_changed', function() {
                        const bounds = editAddressMap.getBounds();
                        if (bounds && editAddressAutocomplete) {
                            editAddressAutocomplete.setBounds(bounds);
                        }
                    });
                    
                    // When a place is selected from autocomplete
                    editAddressAutocomplete.addListener('place_changed', function() {
                        const place = editAddressAutocomplete.getPlace();
                        
                        if (!place.geometry || !place.geometry.location) {
                            return;
                        }
                        
                        // Update marker position
                        editAddressMarker.setPosition(place.geometry.location);
                        
                        // Center map on selected place
                        if (place.geometry.viewport) {
                            editAddressMap.fitBounds(place.geometry.viewport);
                        } else {
                            editAddressMap.setCenter(place.geometry.location);
                            editAddressMap.setZoom(15);
                        }
                        
                        // Update coordinates
                        updateEditAddressCoordinates(place.geometry.location.lat(), place.geometry.location.lng());
                    });
                } catch (error) {
                    console.error('Error initializing Autocomplete:', error);
                }
            }
            
            // Update coordinates when marker is dragged
            editAddressMarker.addListener('dragend', function() {
                const position = editAddressMarker.getPosition();
                updateEditAddressCoordinates(position.lat(), position.lng());
            });
            
            // Update coordinates when map is clicked
            editAddressMap.addListener('click', function(event) {
                const lat = event.latLng.lat();
                const lng = event.latLng.lng();
                editAddressMarker.setPosition({ lat: lat, lng: lng });
                updateEditAddressCoordinates(lat, lng);
            });
            
            // Set coordinates
            updateEditAddressCoordinates(lat, lng);
        }
        
        function updateEditAddressCoordinates(lat, lng) {
            const latInput = document.getElementById('edit_latitude');
            const lngInput = document.getElementById('edit_longitude');
            if (latInput) latInput.value = lat.toFixed(8);
            if (lngInput) lngInput.value = lng.toFixed(8);
        }
        
        // Load states for admin
        function loadAdminStates(callback) {
            const stateSelect = $('#add_state');
            stateSelect.empty().append('<option value="">{{ __("admin.select_state") ?? "Select State" }}</option>');
            stateSelect.prop('disabled', true);
            
            const defaultCountryId = $('#add_country_id').val();
            
            $.ajax({
                url: '{{ route("website.locations.states") }}',
                method: 'GET',
                data: { country_id: defaultCountryId },
                success: function(response) {
                    if (Array.isArray(response)) {
                        if (response.length > 0) {
                            response.forEach(function(state) {
                                stateSelect.append(`<option value="${state.id}">${state.name}</option>`);
                            });
                        }
                        stateSelect.prop('disabled', false);
                    } else {
                        stateSelect.prop('disabled', false);
                    }
                    if (callback) callback();
                },
                error: function() {
                    stateSelect.prop('disabled', false);
                    if (callback) callback();
                }
            });
        }
        
        // Load cities for admin
        function loadAdminCities(stateId, callback) {
            const citySelect = $('#add_city');
            citySelect.empty().append('<option value="">{{ __("admin.select_city") ?? "Select City" }}</option>');
            
            if (!stateId) {
                citySelect.prop('disabled', true);
                if (callback) callback();
                return;
            }
            
            citySelect.prop('disabled', true);
            
            $.ajax({
                url: '{{ route("website.locations.cities") }}',
                method: 'GET',
                data: { state_id: stateId },
                success: function(response) {
                    if (Array.isArray(response)) {
                        response.forEach(function(city) {
                            citySelect.append(`<option value="${city.id}">${city.name}</option>`);
                        });
                        citySelect.prop('disabled', false);
                    }
                    if (callback) callback();
                },
                error: function() {
                    citySelect.prop('disabled', false);
                    if (callback) callback();
                }
            });
        }
        
        // Load states for edit
        function loadEditAdminStates(callback) {
            const stateSelect = $('#edit_state');
            stateSelect.empty().append('<option value="">{{ __("admin.select_state") ?? "Select State" }}</option>');
            stateSelect.prop('disabled', true);
            
            const defaultCountryId = $('#edit_country_id').val();
            
            $.ajax({
                url: '{{ route("website.locations.states") }}',
                method: 'GET',
                data: { country_id: defaultCountryId },
                success: function(response) {
                    if (Array.isArray(response)) {
                        if (response.length > 0) {
                            response.forEach(function(state) {
                                stateSelect.append(`<option value="${state.id}">${state.name}</option>`);
                            });
                        }
                        stateSelect.prop('disabled', false);
                    } else {
                        stateSelect.prop('disabled', false);
                    }
                    if (callback) callback();
                },
                error: function() {
                    stateSelect.prop('disabled', false);
                    if (callback) callback();
                }
            });
        }
        
        // Load cities for edit
        function loadEditAdminCities(stateId, callback) {
            const citySelect = $('#edit_city');
            citySelect.empty().append('<option value="">{{ __("admin.select_city") ?? "Select City" }}</option>');
            
            if (!stateId) {
                citySelect.prop('disabled', true);
                if (callback) callback();
                return;
            }
            
            citySelect.prop('disabled', true);
            
            $.ajax({
                url: '{{ route("website.locations.cities") }}',
                method: 'GET',
                data: { state_id: stateId },
                success: function(response) {
                    if (Array.isArray(response)) {
                        response.forEach(function(city) {
                            citySelect.append(`<option value="${city.id}">${city.name}</option>`);
                        });
                        citySelect.prop('disabled', false);
                    }
                    if (callback) callback();
                },
                error: function() {
                    citySelect.prop('disabled', false);
                    if (callback) callback();
                }
            });
        }
        
        // State change handler for add
        $(document).on('change', '#add_state', function() {
            const stateId = $(this).val();
            const stateName = $(this).find('option:selected').text();
            $('#add_state_name').val(stateName);
            loadAdminCities(stateId);
            $('#add_city_name').val('');
        });
        
        // City change handler for add
        $(document).on('change', '#add_city', function() {
            const cityName = $(this).find('option:selected').text();
            $('#add_city_name').val(cityName);
        });
        
        // State change handler for edit
        $(document).on('change', '#edit_state', function() {
            const stateId = $(this).val();
            const stateName = $(this).find('option:selected').text();
            $('#edit_state_name').val(stateName);
            loadEditAdminCities(stateId);
            $('#edit_city_name').val('');
        });
        
        // City change handler for edit
        $(document).on('change', '#edit_city', function() {
            const cityName = $(this).find('option:selected').text();
            $('#edit_city_name').val(cityName);
        });
        
        // Initialize Add Address Map when modal is shown
        $('#addAddressModal').on('shown.bs.modal', function() {
            loadAdminStates();
            if (typeof google !== 'undefined' && google.maps) {
                setTimeout(function() {
                    initAddAddressMap();
                }, 300);
            }
        });
        
        // Initialize Edit Address Map when modal is shown
        $('#editAddressModal').on('shown.bs.modal', function() {
            loadEditAdminStates();
            if (typeof google !== 'undefined' && google.maps) {
                const lat = parseFloat($('#edit_latitude').val()) || 29.3759;
                const lng = parseFloat($('#edit_longitude').val()) || 47.9774;
                setTimeout(function() {
                    initEditAddressMap(lat, lng);
                }, 300);
            }
        });
    </script>
@endsection
