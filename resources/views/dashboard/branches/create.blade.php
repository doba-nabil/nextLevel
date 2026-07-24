@extends('dashboard.layout.master')
@section('title', 'Branches - Create')

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">Create Branch</h5>
                    <div class="card-body">
                        <form id="formValidationExamples" class="row g-6" method="POST"
                              action="{{ route('branches.store') }}">
                            @csrf

                            <ul class="nav nav-tabs" id="langTabs" role="tablist">
                                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                                id="{{$localeCode}}-tab" data-bs-toggle="tab"
                                                data-bs-target="#{{$localeCode}}" type="button"
                                                role="tab">{{ __('admin.'.$properties['name']) }}</button>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content mt-3 p-3" id="langTabsContent">
                                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                         id="{{$localeCode}}" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label">{{ __('admin.name') }}</label>
                                                <input value="{{ old("name.$localeCode") }}" type="text"
                                                       class="form-control"
                                                       name="name[{{$localeCode}}]"
                                                       placeholder="{{ __('admin.name') }}">
                                                @error("name.$localeCode")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">{{ __('admin.address') }}</label>
                                                <textarea class="form-control" name="address[{{$localeCode}}]"
                                                          placeholder="{{ __('admin.address') }}">{{ old("address.$localeCode") }}</textarea>
                                                @error("address.$localeCode")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.phone') }}</label>
                                    <input value="{{ old('phone') }}" type="text" class="form-control" name="phone"
                                           placeholder="{{ __('admin.phone') }}">
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.whatsapp') }}</label>
                                    <input value="{{ old('whatsapp') }}" type="text" class="form-control"
                                           name="whatsapp"
                                           placeholder="{{ __('admin.whatsapp') }}">
                                    @error('whatsapp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">{{ __('admin.username') }}</label>
                                        <input value="{{ old('username') }}" type="text" class="form-control"
                                               name="username" placeholder="{{ __('admin.username') }}">
                                        @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.password') }}</label>
                                    <input value="" type="password" class="form-control"
                                           name="password" placeholder="{{ __('admin.password') }}">
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.armada_key') ?? 'Armada Key' }}</label>
                                    <input value="{{ old('armada_key') }}" type="text" class="form-control"
                                           name="armada_key" placeholder="{{ __('admin.armada_key') ?? 'Enter Armada API Key' }}">
                                    <small class="form-text text-muted">{{ __('admin.armada_key_description') ?? 'API Key for Armada delivery service (optional)' }}</small>
                                    @error('armada_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <!-- Hidden latitude and longitude inputs (populated by map) -->
                            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                            @error('latitude')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                            @error('longitude')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.select_location_on_map') }}</label>
                                    <div id="branch-map-container" style="position: relative; width: 100%; height: 400px; border-radius: 8px; border: 1px solid #ddd; overflow: hidden;">
                                        <input type="text" id="branch-location-search" class="form-control" 
                                               placeholder="{{ __('admin.search_location') ?? 'Search for a location...' }}" 
                                               autocomplete="new-password"
                                               style="position: absolute; top: 10px; left: 50%; transform: translateX(-50%); width: 90%; z-index: 10; height: 40px; padding: 0 12px; border: 2px solid #007bff; border-radius: 4px; background-color: white;">
                                        <div id="branch-map" style="width: 100%; height: 100%; border-radius: 8px;"></div>
                                    </div>
                                    <small class="text-muted">{{ __('admin.type_to_search_location') ?? 'Type to search for a location or click on the map to set coordinates' }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="countries">{{ __('admin.countries') }}</label>
                                    <select id="countries" class="form-select select2" name="country_id">
                                        <option value="">{{ __('admin.select_country') }}</option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->id }}"
                                                {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                                {{ $country->getTranslation('name', app()->getLocale()) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('country_id')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="states">{{ __('admin.states') }}</label>
                                    <select id="states" class="form-select select2" name="states[]" multiple="multiple" style="width: 100%;">
                                        <option value="">{{ __('admin.select_states') }}</option>
                                    </select>
                                    <small class="form-text text-muted">{{ __('admin.select_multiple_states') }}</small>
                                    @error('states')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="cities">{{ __('admin.delivery_cities') }} <span class="text-danger">*</span></label>
                                    <select id="cities" class="form-select select2" name="cities[]" multiple="multiple" style="width: 100%;">
                                        <option value="">{{ __('admin.select_cities') }}</option>
                                    </select>
                                    <small class="form-text text-muted">{{ __('admin.select_multiple_cities') }}</small>
                                    @error('cities')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    @error('cities.*')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.status') }}</label>
                                    <select id="active" class="select2 form-control" name="active"
                                            style="width:100%;">
                                        <option value="1">{{ __('admin.active') }}</option>
                                        <option value="0">{{ __('admin.deactive') }}</option>
                                    </select>
                                    @error('active')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <hr>
                            <h3>{{ __('admin.working_hours') }}</h3>
                            <div id="working-hours-container">
                                <div class="working-hour-row mb-3">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">{{ __('admin.from_day') }}</label>
                                            <select name="working_hours[0][from_day]" class="form-select select2">
                                                <option value="sunday">{{ __('admin.sunday') }}</option>
                                                <option value="monday">{{ __('admin.monday') }}</option>
                                                <option value="tuesday">{{ __('admin.tuesday') }}</option>
                                                <option value="wednesday">{{ __('admin.wednesday') }}</option>
                                                <option value="thursday">{{ __('admin.thursday') }}</option>
                                                <option value="friday">{{ __('admin.friday') }}</option>
                                                <option value="saturday">{{ __('admin.saturday') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">{{ __('admin.to_day') }}</label>
                                            <select name="working_hours[0][to_day]" class="form-select select2">
                                                <option value="sunday">{{ __('admin.sunday') }}</option>
                                                <option value="monday">{{ __('admin.monday') }}</option>
                                                <option value="tuesday">{{ __('admin.tuesday') }}</option>
                                                <option value="wednesday">{{ __('admin.wednesday') }}</option>
                                                <option value="thursday">{{ __('admin.thursday') }}</option>
                                                <option value="friday">{{ __('admin.friday') }}</option>
                                                <option value="saturday">{{ __('admin.saturday') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">{{ __('admin.from_time') }}</label>
                                            <input type="time" name="working_hours[0][from_time]" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">{{ __('admin.to_time') }}</label>
                                            <input type="time" name="working_hours[0][to_time]" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="add-working-hour" class="btn btn-secondary mb-3">
                                {{ __('admin.add_working_hour') }}
                            </button>
                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('dashboard-head')
    @include('dashboard.partials.create.css')
@endsection

@section('dashboard-footer')
    @include('dashboard.partials.create.js')
    <script>
        'use strict';

        document.addEventListener('DOMContentLoaded', function () {
            const categoryForm = document.getElementById('formValidationExamples');

            if (categoryForm) {
                const fv = FormValidation.formValidation(categoryForm, {
                    fields: {
                        'name[ar]': {
                            validators: {
                                notEmpty: {message: '{{ __('admin.required') }}'},
                                stringLength: {
                                    min: 3,
                                    max: 50,
                                    message: '{{ __('admin.name_length') }}'
                                }
                            }
                        },
                        'name[en]': {
                            validators: {
                                notEmpty: {message: '{{ __('admin.required') }}'},
                                stringLength: {
                                    min: 3,
                                    max: 50,
                                    message: '{{ __('admin.name_length') }}'
                                }
                            }
                        },
                        'address[ar]': {
                            validators: {
                                notEmpty: {message: '{{ __('admin.required') }}'}
                            }
                        },
                        'address[en]': {
                            validators: {
                                notEmpty: {message: '{{ __('admin.required') }}'}
                            }
                        },
                        'phone': {
                            validators: {
                                notEmpty: {message: '{{ __('admin.required') }}'},
                                stringLength: {max: 20, message: '{{ __('admin.phone_length') }}'}
                            }
                        },
                        'whatsapp': {
                            validators: {
                                stringLength: {max: 20, message: '{{ __('admin.whatsapp_length') }}'}
                            }
                        },
                        'latitude': {
                            validators: {
                                numeric: {message: '{{ __('admin.required') }}'},
                                between: {min: -90, max: 90, message: '{{ __('admin.latitude_range') }}'}
                            }
                        },
                        'longitude': {
                            validators: {
                                numeric: {message: '{{ __('admin.required') }}'},
                                between: {min: -180, max: 180, message: '{{ __('admin.longitude_range') }}'}
                            }
                        },
                        'image': {
                            validators: {
                                file: {
                                    extension: 'jpg,jpeg,png',
                                    type: 'image/jpeg,image/jpg,image/png',
                                    message: '{{ __('admin.image_format') }}'
                                }
                            }
                        },
                        'country_id': {
                            validators: {
                                notEmpty: {message: '{{ __('admin.required') }}'}
                            }
                        },
                        'cities[]': {
                            validators: {
                                notEmpty: {message: '{{ __('admin.required') }}'},
                                callback: {
                                    message: '{{ __('admin.please_select_at_least_one_city') }}',
                                    callback: function(input) {
                                        const value = $(input).val();
                                        return value && value.length > 0;
                                    }
                                }
                            }
                        },
                        'working_hours[0][from_day]': {
                            validators: {
                                notEmpty: {message: '{{ __('admin.required') }}'}
                            }
                        },
                        'working_hours[0][to_day]': {
                            validators: {
                                notEmpty: {message: '{{ __('admin.required') }}'}
                            }
                        },
                        'working_hours[0][from_time]': {
                            validators: {
                                callback: {
                                    message: '{{ __('admin.required') }}',
                                    callback: function (input) {
                                        const isClosed = document.querySelector('input[name="working_hours[0][is_closed]"]').checked;
                                        return isClosed || (input.value !== '');
                                    }
                                }
                            }
                        },
                        'working_hours[0][to_time]': {
                            validators: {
                                callback: {
                                    message: '{{ __('admin.required') }}',
                                    callback: function (input) {
                                        const isClosed = document.querySelector('input[name="working_hours[0][is_closed]"]').checked;
                                        return isClosed || (input.value !== '');
                                    }
                                }
                            }
                        }
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap5: new FormValidation.plugins.Bootstrap5(),
                        submitButton: new FormValidation.plugins.SubmitButton(),
                        autoFocus: new FormValidation.plugins.AutoFocus(),
                        defaultSubmit: new FormValidation.plugins.DefaultSubmit()
                    }
                });

                $('#add-working-hour').click(function () {
                    const index = $('.working-hour-row').length;
                    fv.addField(`working_hours[${index}][from_day]`, {
                        validators: {
                            notEmpty: {message: '{{ __('admin.required') }}'}
                        }
                    });
                    fv.addField(`working_hours[${index}][to_day]`, {
                        validators: {
                            notEmpty: {message: '{{ __('admin.required') }}'}
                        }
                    });
                    fv.addField(`working_hours[${index}][from_time]`, {
                        validators: {
                            callback: {
                                message: '{{ __('admin.required') }}',
                                callback: function (input) {
                                    const isClosed = document.querySelector(`input[name="working_hours[${index}][is_closed]"]`).checked;
                                    return isClosed || (input.value !== '');
                                }
                            }
                        }
                    });
                    fv.addField(`working_hours[${index}][to_time]`, {
                        validators: {
                            callback: {
                                message: '{{ __('admin.required') }}',
                                callback: function (input) {
                                    const isClosed = document.querySelector(`input[name="working_hours[${index}][is_closed]"]`).checked;
                                    return isClosed || (input.value !== '');
                                }
                            }
                        }
                    });
                });

                fv.on('core.form.invalid', function () {
                    const firstInvalidField = categoryForm.querySelector('.is-invalid');
                    if (firstInvalidField) {
                        const tabPane = firstInvalidField.closest('.tab-pane');
                        if (tabPane) {
                            const tabId = tabPane.getAttribute('id');
                            const tabTrigger = document.querySelector(`[data-bs-target="#${tabId}"]`);
                            if (tabTrigger) {
                                const tabName = tabTrigger.innerText.trim();
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __('admin.validation_error') }}',
                                    text: ` "${tabName}"{{ __('admin.validation_error_lang') }} `,
                                    confirmButtonText: '{{ __('admin.ok') }}'
                                });
                            }
                        }
                    }
                });
            }
        });

        $(document).ready(function () {
            // تهيئة Select2
            $('.select2').select2({
                placeholder: function () {
                    return $(this).find('option:first').text();
                },
                allowClear: true,
                width: '100%'
            });
            
            // Initialize cities select as multi-select
            $('#cities').select2({
                placeholder: '{{ __("admin.select_cities") }}',
                allowClear: true,
                width: '100%',
                multiple: true
            });

            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            // Initialize states select as multi-select
            $('#states').select2({
                placeholder: '{{ __("admin.select_states") }}',
                allowClear: true,
                width: '100%',
                multiple: true
            });

            // تحميل المحافظات
            const loadStates = debounce(function (countryId, callback) {
                const statesSelect = $('#states');
                const savedStateValues = statesSelect.val() || [];
                statesSelect.empty();
                $('#cities').empty().select2({
                    placeholder: '{{ __("admin.select_cities") }}',
                    allowClear: true,
                    width: '100%',
                    multiple: true
                });

                if (countryId) {
                    $.ajax({
                        url: '{{ route("locations.parents") }}',
                        method: 'GET',
                        data: {type: 'state', country_id: countryId},
                        beforeSend: function () {
                            statesSelect.prop('disabled', true).select2();
                            statesSelect.after('<span class="loading">{{ __("admin.loading") }}...</span>');
                        },
                        success: function (response) {
                            $('.loading').remove();
                            statesSelect.prop('disabled', false);
                            if (Array.isArray(response)) {
                                response.forEach(function (state) {
                                    if (state.id && state.name) {
                                        const isSelected = Array.isArray(savedStateValues) && savedStateValues.includes(String(state.id));
                                        statesSelect.append(`<option value="${state.id}" ${isSelected ? 'selected' : ''}>${state.name}</option>`);
                                    }
                                });
                                statesSelect.select2({
                                    placeholder: '{{ __("admin.select_states") }}',
                                    allowClear: true,
                                    width: '100%',
                                    multiple: true
                                });
                                if (callback) callback();
                            } else {
                                console.error('Invalid AJAX response:', response);
                                alert('{{ __("admin.error_loading_states") }}');
                            }
                        },
                        error: function (xhr) {
                            $('.loading').remove();
                            statesSelect.prop('disabled', false).select2({
                                placeholder: '{{ __("admin.select_states") }}',
                                allowClear: true,
                                width: '100%',
                                multiple: true
                            });
                            console.error('Error loading states:', xhr);
                            alert('{{ __("admin.error_loading_states") }}');
                        }
                    });
                } else {
                    statesSelect.select2({
                        placeholder: '{{ __("admin.select_states") }}',
                        allowClear: true,
                        width: '100%',
                        multiple: true
                    });
                    if (callback) callback();
                }
            }, 300);

            // تحميل المدن من عدة محافظات
            const loadCities = debounce(function (stateIds, callback) {
                const citiesSelect = $('#cities');
                const savedCityValues = citiesSelect.val() || [];
                citiesSelect.empty();
                citiesSelect.select2({
                    placeholder: '{{ __("admin.select_cities") }}',
                    allowClear: true,
                    width: '100%',
                    multiple: true
                });

                if (stateIds && stateIds.length > 0) {
                    // Load cities from all selected states
                    const promises = stateIds.map(function(stateId) {
                        return $.ajax({
                            url: '{{ route("locations.parents") }}',
                            method: 'GET',
                            data: { type: 'city', state_id: stateId }
                        });
                    });

                    citiesSelect.prop('disabled', true).select2();
                    citiesSelect.after('<span class="loading">{{ __("admin.loading") }}...</span>');

                    Promise.all(promises).then(function(responses) {
                        $('.loading').remove();
                        citiesSelect.prop('disabled', false);
                        
                        // Combine all cities from all states and remove duplicates
                        const allCities = [];
                        const cityIds = new Set();
                        
                        responses.forEach(function(response) {
                            if (Array.isArray(response)) {
                                response.forEach(function(city) {
                                    if (city.id && city.name && !cityIds.has(city.id)) {
                                        cityIds.add(city.id);
                                        allCities.push(city);
                                    }
                                });
                            }
                        });

                        // Sort cities by name for better UX
                        allCities.sort(function(a, b) {
                            return a.name.localeCompare(b.name);
                        });

                        // Add cities to select
                        allCities.forEach(function(city) {
                            const isSelected = Array.isArray(savedCityValues) && savedCityValues.includes(String(city.id));
                            citiesSelect.append(`<option value="${city.id}" ${isSelected ? 'selected' : ''}>${city.name}</option>`);
                        });

                        citiesSelect.select2({
                            placeholder: '{{ __("admin.select_cities") }}',
                            allowClear: true,
                            width: '100%',
                            multiple: true
                        });
                        
                        if (callback) callback();
                    }).catch(function(xhr) {
                        $('.loading').remove();
                        citiesSelect.prop('disabled', false).select2({
                            placeholder: '{{ __("admin.select_cities") }}',
                            allowClear: true,
                            width: '100%',
                            multiple: true
                        });
                        console.error('Error loading cities:', xhr);
                        alert('{{ __("admin.error_loading_cities") }}');
                    });
                } else {
                    citiesSelect.select2({
                        placeholder: '{{ __("admin.select_cities") }}',
                        allowClear: true,
                        width: '100%',
                        multiple: true
                    });
                    if (callback) callback();
                }
            }, 300);

            $('#countries').on('change', function () {
                const countryId = $(this).val();
                console.log('Selected country:', countryId);
                loadStates(countryId);
            });

            $('#states').on('change', function () {
                const stateIds = $(this).val();
                console.log('Selected states:', stateIds);
                loadCities(stateIds);
            });
            
            // Real-time validation for city conflicts
            let cityValidationTimeout;
            $('#cities').on('change', function() {
                const selectedCities = $(this).val();
                
                // Clear previous timeout
                clearTimeout(cityValidationTimeout);
                
                // Remove previous error messages
                $('#cities').next('.city-conflict-error').remove();
                $('#cities').removeClass('is-invalid');
                
                if (!selectedCities || selectedCities.length === 0) {
                    return;
                }
                
                // Debounce the validation check
                cityValidationTimeout = setTimeout(function() {
                    checkCityAvailability(selectedCities, null);
                }, 500);
            });
            
            function checkCityAvailability(cityIds, branchId) {
                $.ajax({
                    url: '{{ route("branches.check-city-availability") }}',
                    method: 'POST',
                    data: {
                        city_ids: cityIds,
                        branch_id: branchId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (!response.available && response.conflicts && response.conflicts.length > 0) {
                            let errorMessages = [];
                            response.conflicts.forEach(function(conflict) {
                                const cityName = conflict.city_name || '{{ __("admin.city") }}';
                                const branchName = conflict.branch_name;
                                errorMessages.push('{{ __("admin.city_name_already_assigned_to_branch") ?? "City :city is already assigned to branch: :branch" }}'
                                    .replace(':city', cityName)
                                    .replace(':branch', branchName));
                            });
                            
                            const errorHtml = '<div class="city-conflict-error text-danger mt-2"><small>' + errorMessages.join('<br>') + '</small></div>';
                            $('#cities').after(errorHtml);
                            $('#cities').addClass('is-invalid');
                            
                            // Disable form submission
                            $('form').find('button[type="submit"]').prop('disabled', true);
                        } else {
                            // Enable form submission
                            $('form').find('button[type="submit"]').prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error checking city availability:', xhr);
                    }
                });
            }

            // تحميل القيم المحفوظة (old values)
            @if(old('country_id'))
            $('#countries').val('{{ old("country_id") }}').trigger('change.select2');
            loadStates('{{ old("country_id") }}', function () {
                @if(old('states') && is_array(old('states')))
                const oldStateIds = @json(old('states'));
                $('#states').val(oldStateIds).trigger('change.select2');
                loadCities(oldStateIds, function () {
                    @if(old('location_id'))
                    $('#cities').val('{{ old("location_id") }}').trigger('change.select2');
                    @endif
                });
                @endif
            });
            @endif

            // إضافة فترات عمل ديناميكية
            let hourIndex = 1;
            $('#add-working-hour').click(function () {
                const newRow = `
                    <div class="working-hour-row mb-3">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">{{ __('admin.from_day') }}</label>
                                <select name="working_hours[${hourIndex}][from_day]" class="form-select select2">
                                    <option value="sunday">{{ __('admin.sunday') }}</option>
                                    <option value="monday">{{ __('admin.monday') }}</option>
                                    <option value="tuesday">{{ __('admin.tuesday') }}</option>
                                    <option value="wednesday">{{ __('admin.wednesday') }}</option>
                                    <option value="thursday">{{ __('admin.thursday') }}</option>
                                    <option value="friday">{{ __('admin.friday') }}</option>
                                    <option value="saturday">{{ __('admin.saturday') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('admin.to_day') }}</label>
                                <select name="working_hours[${hourIndex}][to_day]" class="form-select select2">
                                    <option value="sunday">{{ __('admin.sunday') }}</option>
                                    <option value="monday">{{ __('admin.monday') }}</option>
                                    <option value="tuesday">{{ __('admin.tuesday') }}</option>
                                    <option value="wednesday">{{ __('admin.wednesday') }}</option>
                                    <option value="thursday">{{ __('admin.thursday') }}</option>
                                    <option value="friday">{{ __('admin.friday') }}</option>
                                    <option value="saturday">{{ __('admin.saturday') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('admin.from_time') }}</label>
                                <input type="time" name="working_hours[${hourIndex}][from_time]" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('admin.to_time') }}</label>
                                <input type="time" name="working_hours[${hourIndex}][to_time]" class="form-control">
                            </div>

            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-working-hour">{{ __('admin.remove') }}</button>
                            </div>
                        </div>
                    </div>`;
                $('#working-hours-container').append(newRow);
                $('.select2').select2({
                    placeholder: function () {
                        return $(this).find('option:first').text();
                    },
                    allowClear: true,
                    width: '100%'
                });
                hourIndex++;
            });

            // إزالة فترة عمل
            $(document).on('click', '.remove-working-hour', function () {
                const row = $(this).closest('.working-hour-row');
                const index = row.index();
                row.remove();
                fv.removeField(`working_hours[${index}][from_day]`);
                fv.removeField(`working_hours[${index}][to_day]`);
                fv.removeField(`working_hours[${index}][from_time]`);
                fv.removeField(`working_hours[${index}][to_time]`);
            });
        });
    </script>
    <style>
        .loading {
            color: #888;
            font-size: 14px;
            margin-left: 10px;
        }
    </style>
    
    <!-- Google Maps API -->
    @if(config('services.google_maps_api_key'))
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps_api_key') }}&libraries=places&callback=initBranchMap" async defer></script>
    @else
    <script>
        console.error('Google Maps API key is not configured');
    </script>
    @endif
    
    <script>
        let branchMap;
        let branchMarker;
        let branchGeocoder;
        let branchAutocomplete;
        
        function initBranchMap() {
            const mapDiv = document.getElementById('branch-map');
            if (!mapDiv) return;
            
            // Default location (Kuwait)
            const defaultLat = {{ old('latitude', 29.3759) }};
            const defaultLng = {{ old('longitude', 47.9774) }};
            
            branchMap = new google.maps.Map(mapDiv, {
                center: { lat: defaultLat, lng: defaultLng },
                zoom: 13,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true
            });
            
            branchGeocoder = new google.maps.Geocoder();
            
            // Add marker
            branchMarker = new google.maps.Marker({
                map: branchMap,
                draggable: true,
                position: { lat: defaultLat, lng: defaultLng }
            });
            
            // Initialize Autocomplete for search
            const searchInput = document.getElementById('branch-location-search');
            if (searchInput && typeof google !== 'undefined' && google.maps && google.maps.places) {
                try {
                    branchAutocomplete = new google.maps.places.Autocomplete(searchInput, {
                        types: ['geocode', 'establishment'],
                        fields: ['geometry', 'formatted_address', 'address_components', 'name']
                    });
                    
                    // Bias Autocomplete results towards current map's viewport
                    branchMap.addListener('bounds_changed', function() {
                        const bounds = branchMap.getBounds();
                        if (bounds && branchAutocomplete) {
                            branchAutocomplete.setBounds(bounds);
                        }
                    });
                    
                    // When a place is selected from autocomplete
                    branchAutocomplete.addListener('place_changed', function() {
                        const place = branchAutocomplete.getPlace();
                        
                        if (!place.geometry || !place.geometry.location) {
                            return;
                        }
                        
                        // Update marker position
                        branchMarker.setPosition(place.geometry.location);
                        
                        // Center map on selected place
                        if (place.geometry.viewport) {
                            branchMap.fitBounds(place.geometry.viewport);
                        } else {
                            branchMap.setCenter(place.geometry.location);
                            branchMap.setZoom(15);
                        }
                        
                        // Update coordinates
                        updateBranchCoordinates(place.geometry.location.lat(), place.geometry.location.lng());
                    });
                } catch (error) {
                    console.error('Error initializing Autocomplete:', error);
                }
            }
            
            // Update coordinates when marker is dragged
            branchMarker.addListener('dragend', function() {
                const position = branchMarker.getPosition();
                updateBranchCoordinates(position.lat(), position.lng());
            });
            
            // Update coordinates when map is clicked
            branchMap.addListener('click', function(event) {
                const lat = event.latLng.lat();
                const lng = event.latLng.lng();
                branchMarker.setPosition({ lat: lat, lng: lng });
                updateBranchCoordinates(lat, lng);
            });
            
            // Initialize with existing values if available, otherwise use defaults
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            let existingLat = defaultLat;
            let existingLng = defaultLng;
            
            if (latInput && lngInput) {
                const parsedLat = parseFloat(latInput.value);
                const parsedLng = parseFloat(lngInput.value);
                if (parsedLat && parsedLng && !isNaN(parsedLat) && !isNaN(parsedLng)) {
                    existingLat = parsedLat;
                    existingLng = parsedLng;
                } else {
                    // Set default values in hidden inputs
                    updateBranchCoordinates(defaultLat, defaultLng);
                }
            } else {
                // Set default values if inputs don't exist
                updateBranchCoordinates(defaultLat, defaultLng);
            }
            
            const position = { lat: existingLat, lng: existingLng };
            branchMap.setCenter(position);
            branchMarker.setPosition(position);
            updateBranchCoordinates(existingLat, existingLng);
        }
        
        function updateBranchCoordinates(lat, lng) {
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            if (latInput) {
                latInput.value = lat.toFixed(8);
                console.log('Updated latitude:', latInput.value);
            }
            if (lngInput) {
                lngInput.value = lng.toFixed(8);
                console.log('Updated longitude:', lngInput.value);
            }
        }
        
        // Also update map when lat/lng inputs change manually
        document.addEventListener('DOMContentLoaded', function() {
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            
            if (latInput && lngInput) {
                latInput.addEventListener('change', function() {
                    const lat = parseFloat(this.value);
                    const lng = parseFloat(lngInput.value);
                    if (!isNaN(lat) && !isNaN(lng) && branchMap && branchMarker) {
                        const position = { lat: lat, lng: lng };
                        branchMap.setCenter(position);
                        branchMarker.setPosition(position);
                    }
                });
                
                lngInput.addEventListener('change', function() {
                    const lat = parseFloat(latInput.value);
                    const lng = parseFloat(this.value);
                    if (!isNaN(lat) && !isNaN(lng) && branchMap && branchMarker) {
                        const position = { lat: lat, lng: lng };
                        branchMap.setCenter(position);
                        branchMarker.setPosition(position);
                    }
                });
            }
        });
    </script>
@endsection
