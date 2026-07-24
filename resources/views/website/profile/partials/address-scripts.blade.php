<script>
(function() {

    // View addresses on map button
    $(document).off('click.addresses', '#view-addresses-map-btn');
    $(document).on('click.addresses', '#view-addresses-map-btn', function(e) {
        e.preventDefault();
        openAddressesMapModal();
    });

    // Add address button - use event delegation for dynamically loaded content
    $(document).off('click.addresses', '#add-address-btn');
    $(document).on('click.addresses', '#add-address-btn', function(e) {
        e.preventDefault();

        resetAddressForm();
        $('#addressModalLabel').text('{{ __("website.add_address") }}');

        setTimeout(function() {
            const modalElement = document.getElementById('addressModal');
            if (modalElement) {
                try {
                    let modal = bootstrap.Modal.getInstance(modalElement);
                    if (!modal) {
                        modal = new bootstrap.Modal(modalElement);
                    }
                    modal.show();
                } catch (error) {
                    if (typeof $.fn.modal !== 'undefined') {
                        $('#addressModal').modal('show');
                    } else {
                        $(modalElement).addClass('show').css('display', 'block');
                        $('body').append('<div class="modal-backdrop fade show"></div>');
                    }
                }
            } else {
                AppSwal.error('Modal not found. Please refresh the page.', '{{ __("website.error") }}');
            }
        }, 100);
    });

    // Edit address button
    $(document).off('click.addresses', '.edit-address-btn');
    $(document).on('click.addresses', '.edit-address-btn', function() {
        const addressId = $(this).data('address-id');
        loadAddressForEdit(addressId);
    });

    // Delete address button
    $(document).off('click.addresses', '.delete-address-btn');
    $(document).on('click.addresses', '.delete-address-btn', function() {
        const addressId = $(this).data('address-id');
        deleteAddress(addressId);
    });

    // Set main address via radio button
    $(document).off('change.addresses', 'input[name="main_address"]');
    $(document).on('change.addresses', 'input[name="main_address"]', function() {
        const addressId = $(this).val();
        if (addressId) {
            setMainAddress(addressId);
        }
    });

    // Save address form
    $('#address-form').off('submit.addresses');
    $('#address-form').on('submit.addresses', function(e) {
        e.preventDefault();
        saveAddress();
    });

    function saveAddress() {
        const addressId = $('#address_id').val();
        const url = addressId
            ? '{{ route("addresses.update", ":id") }}'.replace(':id', addressId)
            : '{{ route("addresses.store") }}';
        const method = addressId ? 'PUT' : 'POST';

        const isMainChecked = $('#is_main').is(':checked');
        const isMain = isMainChecked ? true : false;

        const stateName = $('#state_name').val() || $('#state option:selected').text() || '';
        const cityName = $('#city_name').val() || $('#city option:selected').text() || '';

        let addressText = $('#address').val();
        if (!addressText || addressText.trim() === '') {
            const addressParts = [];
            if ($('#area').val()) addressParts.push($('#area').val());
            if ($('#block').val()) addressParts.push($('#block').val());
            if ($('#street').val()) addressParts.push($('#street').val());
            if ($('#avenue').val()) addressParts.push($('#avenue').val());
            if ($('#building').val()) addressParts.push($('#building').val());
            if ($('#floor').val()) addressParts.push('Floor: ' + $('#floor').val());
            if ($('#apartment').val()) addressParts.push('Apt: ' + $('#apartment').val());
            if ($('#city option:selected').text() && $('#city option:selected').text() !== '{{ __("website.select_city") ?? "Select City" }}') {
                addressParts.push($('#city option:selected').text());
            }
            if ($('#state option:selected').text() && $('#state option:selected').text() !== '{{ __("website.select_state") ?? "Select State" }}') {
                addressParts.push($('#state option:selected').text());
            }
            addressText = addressParts.join(', ');
        }

        const formData = {
            _token: '{{ csrf_token() }}',
            title: $('#address_title').val(),
            address: addressText,
            country: $('#country').val(),
            state: stateName,
            city: cityName,
            area: $('#area').val(),
            block: $('#block').val(),
            street: $('#street').val(),
            avenue: $('#avenue').val(),
            building: $('#building').val(),
            floor: $('#floor').val(),
            apartment: $('#apartment').val(),
            additional_directions: $('#additional_directions').val(),
            latitude: $('#latitude').val(),
            longitude: $('#longitude').val(),
            is_main: isMain
        };

        $.ajax({
            url: url,
            method: method,
            data: formData,
            beforeSend: function() {
                $('#save-address-btn').prop('disabled', true).text('{{ __("website.saving") }}...');
                $('#address-form-error').hide();
            },
            success: function(response) {
                if (response.success) {
                    AppSwal.success(response.message, '{{ __("website.success") }}').then(function() {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                let errorMsg = '{{ __("website.something_went_wrong") }}';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMsg = errors.join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                $('#address-form-error').html(errorMsg).show();
            },
            complete: function() {
                $('#save-address-btn').prop('disabled', false).text('{{ __("website.save") }}');
            }
        });
    }

    function deleteAddress(addressId) {
        AppSwal.confirm({
            title: '{{ __("website.are_you_sure") }}',
            text: '{{ __("website.delete_address_confirmation") }}',
            icon: 'warning',
            confirmButtonText: '{{ __("website.delete") }}',
            cancelButtonText: '{{ __("website.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("addresses.destroy", ":id") }}'.replace(':id', addressId),
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            AppSwal.success(response.message, '{{ __("website.success") }}').then(function() {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        AppSwal.error(xhr.responseJSON?.message || '{{ __("website.something_went_wrong") }}', '{{ __("website.error") }}');
                    }
                });
            }
        });
    }

    function setMainAddress(addressId) {
        $.ajax({
            url: '{{ route("addresses.set.main", ":id") }}'.replace(':id', addressId),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    AppSwal.success(response.message, '{{ __("website.success") }}').then(function() {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                AppSwal.error(xhr.responseJSON?.message || '{{ __("website.something_went_wrong") }}', '{{ __("website.error") }}');
            }
        });
    }

    function checkMainAddressStatus() {
        $.ajax({
            url: '{{ route("addresses.index") }}',
            method: 'GET',
            success: function(response) {
                if (response.success && response.addresses) {
                    const hasMainAddress = response.addresses.some(addr => addr.is_main === true);
                    if (!hasMainAddress) {
                        $('#is_main').prop('checked', true);
                    } else {
                        $('#is_main').prop('checked', false);
                    }
                } else {
                    $('#is_main').prop('checked', true);
                }
            },
            error: function() {
                $('#is_main').prop('checked', true);
            }
        });
    }

    function loadStates() {
        const stateSelect = $('#state');
        stateSelect.empty().append('<option value="">{{ __("website.select_state") ?? "Select State" }}</option>');
        stateSelect.prop('disabled', true);

        const defaultCountryId = $('#country_id').val();

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
            },
            error: function(xhr, status, error) {
                stateSelect.prop('disabled', false);
            }
        });
    }

    function loadCities(stateId) {
        const citySelect = $('#city');
        citySelect.empty().append('<option value="">{{ __("website.select_city") ?? "Select City" }}</option>');

        if (!stateId) {
            citySelect.prop('disabled', true);
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
            },
            error: function() {
                citySelect.prop('disabled', false);
            }
        });
    }

    function resetAddressForm() {
        $('#address-form')[0].reset();
        $('#address_id').val('');
        $('#address-form-error').hide();
        if (window.addressFormMapMarker) {
            window.addressFormMapMarker.setMap(null);
            window.addressFormMapMarker = null;
        }
        if (window.addressFormMap) {
            const defaultPosition = { lat: 29.3759, lng: 47.9774 };
            window.addressFormMap.setCenter(defaultPosition);
            window.addressFormMap.setZoom(13);
            $('#latitude').val('29.3759');
            $('#longitude').val('47.9774');
        }
        if ($('#profile-address-search').length) {
            $('#profile-address-search').val('');
        }

        $('#state').val('').trigger('change');
        $('#city').val('').prop('disabled', true);
        $('#state_name').val('');
        $('#city_name').val('');

        loadStates();
        checkMainAddressStatus();
    }

    $(document).on('change', '#state', function() {
        const stateId = $(this).val();
        const stateName = $(this).find('option:selected').text();
        $('#state_name').val(stateName);
        loadCities(stateId);
        $('#city_name').val('');
    });

    $(document).on('change', '#city', function() {
        const cityId = $(this).val();
        const cityName = $(this).find('option:selected').text();
        $('#city_name').val(cityName);
    });

    $(document).on('shown.bs.modal', '#addressModal', function() {
        if ($('#state option').length <= 1) {
            loadStates();
        }
        if (!$('#address_id').val()) {
            checkMainAddressStatus();
        }

        setTimeout(function() {
            if (!window.addressFormMap) {
                initAddressFormMap();
            } else {
                google.maps.event.trigger(window.addressFormMap, 'resize');
            }
        }, 300);
    });

    function initAddressFormMap() {
        const mapContainer = document.getElementById('profile-address-map');
        if (!mapContainer) {
            return;
        }

        if (mapContainer.offsetWidth === 0 || mapContainer.offsetHeight === 0) {
            setTimeout(function() {
                initAddressFormMap();
            }, 200);
            return;
        }

        let retryCount = 0;
        const maxRetries = 50;

        function waitForGoogleMaps() {
            if (typeof google !== 'undefined' && typeof google.maps !== 'undefined' && typeof google.maps.places !== 'undefined') {
                const defaultLat = parseFloat($('#latitude').val()) || 29.3759;
                const defaultLng = parseFloat($('#longitude').val()) || 47.9774;

                try {
                    window.addressFormMap = new google.maps.Map(mapContainer, {
                        center: { lat: defaultLat, lng: defaultLng },
                        zoom: $('#latitude').val() && $('#longitude').val() ? 15 : 12,
                        mapTypeControl: true,
                        streetViewControl: true,
                        fullscreenControl: true
                    });

                    const geocoder = new google.maps.Geocoder();

                    window.addressFormMapMarker = new google.maps.Marker({
                        position: { lat: defaultLat, lng: defaultLng },
                        map: window.addressFormMap,
                        draggable: true,
                        animation: google.maps.Animation.DROP
                    });

                    google.maps.event.addListener(window.addressFormMapMarker, 'position_changed', function() {
                        const lat = window.addressFormMapMarker.getPosition().lat();
                        const lng = window.addressFormMapMarker.getPosition().lng();
                        $('#latitude').val(lat);
                        $('#longitude').val(lng);
                        reverseGeocodeAddressForm(lat, lng, geocoder);
                    });

                    const searchInput = document.getElementById('profile-address-search');
                    if (searchInput) {
                        window.addressFormAutocomplete = new google.maps.places.Autocomplete(searchInput, {
                            types: ['geocode', 'establishment'],
                            fields: ['geometry', 'formatted_address', 'address_components', 'name']
                        });

                        window.addressFormMap.addListener('bounds_changed', function() {
                            const bounds = window.addressFormMap.getBounds();
                            if (bounds && window.addressFormAutocomplete) {
                                window.addressFormAutocomplete.setBounds(bounds);
                            }
                        });

                        window.addressFormAutocomplete.addListener('place_changed', function() {
                            const place = window.addressFormAutocomplete.getPlace();

                            if (!place.geometry || !place.geometry.location) {
                                return;
                            }

                            window.addressFormMapMarker.setPosition(place.geometry.location);
                            const lat = place.geometry.location.lat();
                            const lng = place.geometry.location.lng();
                            $('#latitude').val(lat);
                            $('#longitude').val(lng);

                            fillAddressFromPlace(place);

                            if (place.geometry.viewport) {
                                window.addressFormMap.fitBounds(place.geometry.viewport);
                            } else {
                                window.addressFormMap.setCenter(place.geometry.location);
                                window.addressFormMap.setZoom(15);
                            }
                        });
                    }

                    window.addressFormMap.addListener('click', function(event) {
                        const lat = event.latLng.lat();
                        const lng = event.latLng.lng();

                        window.addressFormMapMarker.setPosition(event.latLng);
                        $('#latitude').val(lat);
                        $('#longitude').val(lng);
                        reverseGeocodeAddressForm(lat, lng, geocoder);
                    });

                    $(document).off('click', '#locate-me-profile').on('click', '#locate-me-profile', function() {
                        if (window.addressFormMap && window.addressFormMapMarker) {
                            const geocoder = new google.maps.Geocoder();
                            locateUserOnMap(window.addressFormMap, window.addressFormMapMarker, geocoder);
                        }
                    });

                    if ($('#latitude').val() && $('#longitude').val()) {
                        const lat = parseFloat($('#latitude').val());
                        const lng = parseFloat($('#longitude').val());
                        if (!isNaN(lat) && !isNaN(lng)) {
                            const position = { lat: lat, lng: lng };
                            window.addressFormMap.setCenter(position);
                            window.addressFormMap.setZoom(15);
                            window.addressFormMapMarker.setPosition(position);
                        }
                    }

                    setTimeout(function() {
                        if (window.addressFormMap) {
                            google.maps.event.trigger(window.addressFormMap, 'resize');
                        }
                    }, 100);
                } catch (error) {
                }
            } else {
                retryCount++;
                if (retryCount >= maxRetries) {
                    return;
                }
                setTimeout(waitForGoogleMaps, 100);
            }
        }

        waitForGoogleMaps();
    }

    function reverseGeocodeAddressForm(lat, lng, geocoder) {
        geocoder.geocode({ location: { lat: lat, lng: lng } }, function(results, status) {
            if (status === 'OK' && results[0]) {
                fillAddressFromPlace(results[0]);
            }
        });
    }

    // Flag to prevent multiple simultaneous location requests
    let isLocating = false;

    function locateUserOnMap(map, marker, geocoder) {
        if (!navigator.geolocation) {
            AppSwal.error('{{ __("website.geolocation_not_supported") ?? "Geolocation is not supported" }}', '{{ __("website.error") }}');
            return;
        }

        // Prevent multiple simultaneous requests
        if (isLocating) {
            return;
        }

        isLocating = true;
        const locateBtn = $('#locate-me-profile');
        const originalText = locateBtn.html();
        locateBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __("website.locating") ?? "Locating..." }}');

        // Store timeout ID to clear it if needed
        let timeoutId = null;

        navigator.geolocation.getCurrentPosition(
            function(position) {
                // Clear timeout if position was obtained
                if (timeoutId) {
                    clearTimeout(timeoutId);
                    timeoutId = null;
                }

                isLocating = false;
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const userLocation = { lat: lat, lng: lng };

                map.setCenter(userLocation);
                map.setZoom(15);
                marker.setPosition(userLocation);
                $('#latitude').val(lat);
                $('#longitude').val(lng);

                if (geocoder) {
                    reverseGeocodeAddressForm(lat, lng, geocoder);
                }

                locateBtn.prop('disabled', false).html(originalText);
            },
            function(error) {
                // Clear timeout
                if (timeoutId) {
                    clearTimeout(timeoutId);
                    timeoutId = null;
                }

                isLocating = false;
                locateBtn.prop('disabled', false).html(originalText);

                // Only show specific error messages, not the generic "Failed to get your location"
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        AppSwal.error(message, '{{ __("website.error") }}');
                        break;
                    case error.POSITION_UNAVAILABLE:
                        AppSwal.error('{{ __("website.location_unavailable") ?? "Location information is unavailable. Please try again." }}', '{{ __("website.error") }}');
                        break;
                    case error.TIMEOUT:
                        // For timeout, don't show error - user might have slow connection
                        // Just reset the button silently
                        break;
                    default:
                        // Don't show error for unknown errors - just reset the button
                        break;
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 20000, // Increased timeout to 20 seconds
                maximumAge: 0
            }
        );

        // Fallback timeout to reset button if getCurrentPosition doesn't respond
        timeoutId = setTimeout(function() {
            if (isLocating) {
                isLocating = false;
                locateBtn.prop('disabled', false).html(originalText);
            }
        }, 25000); // 25 seconds fallback
    }

    function fillAddressFromPlace(place) {
        $('#address').val(place.formatted_address || '');
        $('#state').val('');
        $('#city').val('');
        $('#area').val('');
        $('#block').val('');
        $('#street').val('');
        $('#building').val('');
        $('#floor').val('');
        $('#apartment').val('');

        if (place.address_components) {
            place.address_components.forEach(function(component) {
                const types = component.types;
                const value = component.long_name;
                if (types.includes('administrative_area_level_1')) {
                    const stateOption = $('#state option').filter(function() {
                        return $(this).text().toLowerCase().includes(value.toLowerCase()) ||
                               value.toLowerCase().includes($(this).text().toLowerCase());
                    }).first();
                    if (stateOption.length > 0) {
                        $('#state').val(stateOption.val()).trigger('change');
                        $('#state_name').val(stateOption.text());
                    }
                }
                if (types.includes('locality')) {
                    const cityOption = $('#city option').filter(function() {
                        return $(this).text().toLowerCase().includes(value.toLowerCase()) ||
                               value.toLowerCase().includes($(this).text().toLowerCase());
                    }).first();
                    if (cityOption.length > 0) {
                        $('#city').val(cityOption.val());
                        $('#city_name').val(cityOption.text());
                    }
                }
                if (types.includes('sublocality') || types.includes('sublocality_level_1')) {
                    $('#area').val(value);
                }
                if (types.includes('route')) {
                    $('#street').val(value);
                }
            });
        }
    }

    function loadAddressForEdit(addressId) {
        $.get('{{ route("addresses.index") }}', function(response) {
            if (response.success) {
                const address = response.addresses.find(a => a.id == addressId);
                if (address) {
                    $('#address_id').val(address.id);
                    $('#address_title').val(address.title || '');
                    $('#address').val(address.address || '');
                    $('#area').val(address.area || '');
                    $('#block').val(address.block || '');
                    $('#street').val(address.street || '');
                    $('#avenue').val(address.avenue || '');
                    $('#building').val(address.building || '');
                    $('#floor').val(address.floor || '');
                    $('#apartment').val(address.apartment || '');
                    $('#additional_directions').val(address.additional_directions || '');
                    $('#latitude').val(address.latitude || '');
                    $('#longitude').val(address.longitude || '');
                    $('#is_main').prop('checked', address.is_main);

                    loadStates();

                    setTimeout(function() {
                        if (address.state) {
                            const stateOption = $('#state option').filter(function() {
                                return $(this).text().trim() === address.state.trim();
                            });
                            if (stateOption.length > 0) {
                                $('#state').val(stateOption.val()).trigger('change');
                                $('#state_name').val(address.state);

                                setTimeout(function() {
                                    if (address.city) {
                                        const cityOption = $('#city option').filter(function() {
                                            return $(this).text().trim() === address.city.trim();
                                        });
                                        if (cityOption.length > 0) {
                                            $('#city').val(cityOption.val());
                                            $('#city_name').val(address.city);
                                        }
                                    }
                                }, 500);
                            }
                        }
                    }, 500);

                    $('#addressModalLabel').text('{{ __("website.edit_address") }}');

                    setTimeout(function() {
                        const modalElement = document.getElementById('addressModal');
                        if (modalElement) {
                            try {
                                let modal = bootstrap.Modal.getInstance(modalElement);
                                if (!modal) {
                                    modal = new bootstrap.Modal(modalElement);
                                }
                                modal.show();

                                setTimeout(function() {
                                    if (!window.addressFormMap) {
                                        initAddressFormMap();
                                    } else {
                                        if (address.latitude && address.longitude) {
                                            const lat = parseFloat(address.latitude);
                                            const lng = parseFloat(address.longitude);
                                            if (!isNaN(lat) && !isNaN(lng)) {
                                                const position = { lat: lat, lng: lng };
                                                window.addressFormMap.setCenter(position);
                                                window.addressFormMap.setZoom(15);
                                                if (window.addressFormMapMarker) {
                                                    window.addressFormMapMarker.setPosition(position);
                                                }
                                            }
                                        }
                                        google.maps.event.trigger(window.addressFormMap, 'resize');
                                    }
                                }, 500);
                            } catch (error) {
                                if (typeof $.fn.modal !== 'undefined') {
                                    $('#addressModal').modal('show');
                                }
                            }
                        }
                    }, 100);
                }
            }
        });
    }

    let addressesMap = null;
    let addressMarkers = [];
    let infoWindow = null;

    function openAddressesMapModal() {
        const modalElement = document.getElementById('addressesMapModal');
        if (modalElement) {
            try {
                let modal = bootstrap.Modal.getInstance(modalElement);
                if (!modal) {
                    modal = new bootstrap.Modal(modalElement);
                }
                modal.show();

                function waitForGoogleMaps() {
                    if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                        setTimeout(function() {
                            loadAddressesForMap();
                        }, 300);
                    } else {
                        setTimeout(waitForGoogleMaps, 100);
                    }
                }

                modalElement.addEventListener('shown.bs.modal', function onShown() {
                    waitForGoogleMaps();
                    modalElement.removeEventListener('shown.bs.modal', onShown);
                }, { once: true });

                modalElement.addEventListener('hidden.bs.modal', function onHidden() {
                    addressMarkers.forEach(marker => marker.setMap(null));
                    addressMarkers = [];
                    modalElement.removeEventListener('hidden.bs.modal', onHidden);
                }, { once: true });
            } catch (error) {
            }
        }
    }

    function loadAddressesForMap() {
        $.get('{{ route("addresses.index") }}', function(response) {
            if (response.success && response.addresses) {
                displayAddressesOnMap(response.addresses);
                displayAddressesList(response.addresses);
            }
        });
    }

    function displayAddressesOnMap(addresses) {
        const mapDiv = document.getElementById('addresses-map');
        if (!mapDiv) return;

        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
            mapDiv.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100"><p class="text-danger">Google Maps is not loaded.</p></div>';
            return;
        }

        addressMarkers.forEach(marker => marker.setMap(null));
        addressMarkers = [];

        const validAddresses = addresses.filter(addr => addr.latitude && addr.longitude);

        if (validAddresses.length === 0) {
            mapDiv.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100"><p class="text-muted">{{ __("website.no_addresses_found") }}</p></div>';
            return;
        }

        if (!addressesMap) {
            let avgLat = 0, avgLng = 0;
            validAddresses.forEach(addr => {
                avgLat += parseFloat(addr.latitude);
                avgLng += parseFloat(addr.longitude);
            });
            avgLat /= validAddresses.length;
            avgLng /= validAddresses.length;

            addressesMap = new google.maps.Map(mapDiv, {
                center: { lat: avgLat, lng: avgLng },
                zoom: 12,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true
            });

            infoWindow = new google.maps.InfoWindow();
        } else {
            let avgLat = 0, avgLng = 0;
            validAddresses.forEach(addr => {
                avgLat += parseFloat(addr.latitude);
                avgLng += parseFloat(addr.longitude);
            });
            avgLat /= validAddresses.length;
            avgLng /= validAddresses.length;
            addressesMap.setCenter({ lat: avgLat, lng: avgLng });
        }

        validAddresses.forEach(address => {
            const position = {
                lat: parseFloat(address.latitude),
                lng: parseFloat(address.longitude)
            };

            const icon = {
                url: address.is_main
                    ? 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
                    : 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                scaledSize: new google.maps.Size(32, 32),
                anchor: new google.maps.Point(16, 32)
            };

            const marker = new google.maps.Marker({
                position: position,
                map: addressesMap,
                icon: icon,
                title: address.title || address.full_address,
                animation: address.is_main ? google.maps.Animation.BOUNCE : null
            });

            const infoContent = `
                <div class="p-2">
                    <h6 class="mb-2">${address.title || '{{ __("website.address") }} #' + address.id}</h6>
                    ${address.is_main ? '<span class="badge bg-primary mb-2">{{ __("website.main_address") }}</span><br>' : ''}
                    <p class="mb-2"><strong>{{ __("website.address") }}:</strong><br>${address.full_address || address.address || ''}</p>
                </div>
            `;

            marker.addListener('click', function() {
                infoWindow.setContent(infoContent);
                infoWindow.open(addressesMap, marker);
                $('.address-item-map').removeClass('active');
                $(`.address-item-map[data-address-id="${address.id}"]`).addClass('active');
            });

            addressMarkers.push(marker);
        });

        if (validAddresses.length > 1) {
            const bounds = new google.maps.LatLngBounds();
            addressMarkers.forEach(marker => bounds.extend(marker.getPosition()));
            addressesMap.fitBounds(bounds);
        }
    }

    function displayAddressesList(addresses) {
        const listContainer = $('#addresses-map-list');
        listContainer.empty();

        if (addresses.length === 0) {
            listContainer.html('<div class="alert alert-info">{{ __("website.no_addresses_found") }}</div>');
            return;
        }

        addresses.forEach(address => {
            const addressItem = $(`
                <div class="list-group-item address-item-map ${address.is_main ? 'active border-primary' : ''}"
                     data-address-id="${address.id}"
                     data-lat="${address.latitude || ''}"
                     data-lng="${address.longitude || ''}"
                     style="cursor: pointer;">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                ${address.title || '{{ __("website.address") }} #' + address.id}
                                ${address.is_main ? '<span class="badge bg-primary ms-2">{{ __("website.main_address") }}</span>' : ''}
                            </h6>
                            <p class="mb-1 small text-muted">${address.full_address || address.address || ''}</p>
                        </div>
                    </div>
                </div>
            `);

            if (address.latitude && address.longitude) {
                addressItem.on('click', function() {
                    const lat = parseFloat($(this).data('lat'));
                    const lng = parseFloat($(this).data('lng'));

                    if (addressesMap) {
                        addressesMap.setCenter({ lat: lat, lng: lng });
                        addressesMap.setZoom(15);

                        const marker = addressMarkers.find(m =>
                            Math.abs(m.getPosition().lat() - lat) < 0.0001 &&
                            Math.abs(m.getPosition().lng() - lng) < 0.0001
                        );
                        if (marker) {
                            google.maps.event.trigger(marker, 'click');
                        }
                    }

                    $('.address-item-map').removeClass('active border-primary');
                    $(this).addClass('active border-primary');
                });
            }

            listContainer.append(addressItem);
        });
    }
})();
</script>

<!-- Google Maps API -->
@if(config('services.google_maps_api_key'))
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps_api_key') }}&libraries=places" async defer></script>
@else
<script>
</script>
@endif
