@extends('dashboard.layout.master')
@section('title', __('admin.create').' . '. __('admin.'.$type))
@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ __('admin.create').'  '. __('admin.'.$type) }}</h5>
                    <div class="card-body">
                        <form id="formValidationExamples" class="row g-6" method="POST"
                              action="{{ route($type.'.store') }}">
                            @csrf
                            <input name="type" value="{{$type_request}}" hidden=""/>

                            <ul class="nav nav-tabs" id="langTabs" role="tablist">
                                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                                id="{{$localeCode}}-tab" data-bs-toggle="tab"
                                                data-bs-target="#{{$localeCode}}" type="button"
                                                role="tab">{{ __('admin.'.$properties['name']) }}
                                        </button>
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
                                                       class="form-control" name="name[{{$localeCode}}]"
                                                       placeholder="{{ __('admin.name') }}">
                                                @error("name.$localeCode")
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($type == 'states')
                                <div class="form-group">
                                    <label for="countries">{{ __('admin.countries') }}</label>
                                    <select id="countries" class="select2 form-control" name="parent_id"
                                            style="width:100%;">
                                        <option value="">{{ __('admin.select_parent') }}</option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->id }}"
                                                {{ old('parent_id') == $country->id ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('parent_id')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif

                            @if($type == 'cities')
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="countries">{{ __('admin.countries') }}</label>
                                        <select id="countries" class="select2 form-control" style="width:100%;">
                                            <option value="">{{ __('admin.select_country') }}</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}"
                                                    {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                                    {{ $country->name }}
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
                                        <select id="states" class="select2 form-control" name="parent_id"
                                                style="width:100%;">
                                            @if(old('country_id'))
                                                    <?php $states = \App\Models\Location::where('type', 'state')->where('parent_id', old('country_id'))->get(); ?>
                                                @foreach($states as $state)
                                                    <option
                                                        value="{{ $state->id }}" {{ old('parent_id') == $state->id ? 'selected' : '' }}>
                                                        {{ $state->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('parent_id')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">{{ __('admin.shipping_fee') . ' ' . __('admin.within_city') }}</label>
                                        <input value="{{ old('shipping_fee_near') }}"
                                               type="number" min="0" step="0.1" class="form-control"
                                               name="shipping_fee_near"
                                               placeholder="{{ __('admin.shipping_fee') . ' ' . __('admin.within_city') }}">
                                        @error("shipping_fee_near")
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">{{ __('admin.shipping_fee') . ' ' . __('admin.outside_city') }}</label>
                                        <input value="{{ old('shipping_fee_far') }}"
                                               type="number" min="0" step="0.1" class="form-control"
                                               name="shipping_fee_far"
                                               placeholder="{{ __('admin.shipping_fee') . ' ' . __('admin.outside_city') }}">
                                        @error("shipping_fee_far")
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">{{ __('admin.min_order') . ' ' . __('admin.within_city') }}</label>
                                        <input value="{{ old('min_order_near') }}"
                                               type="number" min="0" step="0.1" class="form-control"
                                               name="min_order_near"
                                               placeholder="{{ __('admin.min_order') . ' ' . __('admin.within_city') }}">
                                        @error("min_order_near")
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">{{ __('admin.min_order') . ' ' . __('admin.outside_city') }}</label>
                                        <input value="{{ old('min_order_far') }}"
                                               type="number" min="0" step="0.1" class="form-control"
                                               name="min_order_far"
                                               placeholder="{{ __('admin.min_order') . ' ' . __('admin.outside_city') }}">
                                        @error("min_order_far")
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">{{ __('admin.delivery_time') }}</label>
                                        <input value="{{ old('delivery_time') }}"
                                               type="number" min="0.01" step="0.01" class="form-control"
                                               name="delivery_time"
                                               placeholder="{{ __('admin.delivery_time') }}">
                                        <small class="text-muted">{{ __('admin.delivery_time_hint') }}</small>
                                        @error("delivery_time")
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                            @if($type == 'countries')
                                <div class="row g-3 mt-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('admin.code') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="code" value="{{ old('code') }}"
                                                   placeholder="KW" maxlength="2" style="text-transform: uppercase;">
                                            @error('code')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <small class="text-muted">{{ __('admin.iso_country_code_2_letters') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('admin.phone_code') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="phone_code" value="{{ old('phone_code') }}"
                                                   placeholder="+966">
                                            @error('phone_code')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <h6 class="card-header m-0 p-0">{{  __('admin.currency') }}</h6>
                                <ul class="nav nav-tabs" id="langTabss" role="tablist">
                                    @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                                    id="{{$localeCode}}-tab" data-bs-toggle="tab"
                                                    data-bs-target="#{{$localeCode}}-currency" type="button"
                                                    role="tab">{{ __('admin.'.$properties['name']) }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="tab-content mt-3 p-3" id="langTabsContentt">
                                    @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                             id="{{$localeCode}}-currency" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label class="form-label">{{ __('admin.name') }}</label>
                                                    <input value="{{ old("currency_name.$localeCode") }}" type="text"
                                                           class="form-control" name="currency_name[{{$localeCode}}]"
                                                           placeholder="{{ __('admin.name') }}">
                                                    @error("currency_name.$localeCode")
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">{{ __('admin.sign') }}</label>
                                        <input value="{{ old('sign') }}"
                                               type="text" class="form-control" name="sign"
                                               placeholder="{{ __('admin.sign') }}">
                                        @error("sign")
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">{{ __('admin.minimum_usable_points') }}</label>
                                        <input value="{{ old('minimum_usable_points') }}"
                                               type="number" min="0" step="1" class="form-control" name="minimum_usable_points"
                                               placeholder="{{ __('admin.minimum_usable_points') }}">
                                        @error("minimum_usable_points")
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label class="form-label">{{ __('admin.rate_per_point') }}</label>
                                        <input value="{{ old('rate_per_point') }}"
                                               type="number" min="0" step="0.1" class="form-control"
                                               name="rate_per_point"
                                               placeholder="{{ __('admin.rate_per_point_placeholder') }}">
                                        <small
                                            class="form-text text-primary">{{ __('admin.rate_per_point_hint') }}</small>
                                        @error("rate_per_point")
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">{{ __('admin.points_per_currency') }}</label>
                                        <input value="{{ old('points_per_currency') }}"
                                               type="number" min="0" step="0.1" class="form-control"
                                               name="points_per_currency"
                                               placeholder="{{ __('admin.points_per_currency_placeholder') }}">
                                        <small
                                            class="form-text text-primary">{{ __('admin.points_per_currency_hint') }}</small>
                                        @error("points_per_currency")
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                            @endif

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
    @php
        $messages = [
            'name_required' => __('admin.name_required'),
            'required' => __('admin.required'),
            'name_length' => __('admin.name_length'),
            'email_required' => __('admin.email_required'),
            'email_valid' => __('admin.email_valid'),
            'password_required' => __('admin.password_required'),
            'password_length' => __('admin.password_length'),
            'password_confirm' => __('admin.password_confirm'),
        ];
    @endphp
    <script>
        'use strict';
        $(document).ready(function () {
            // تفعيل Select2
            $('.select2').select2({
                placeholder: function () {
                    return $(this).find('option:first').text();
                },
                allowClear: true,
                width: '100%'
            });

            // FormValidation
            const categoryForm = document.getElementById('formValidationExamples');
            if (categoryForm) {
                const messages = @json($messages);
                const fv = FormValidation.formValidation(categoryForm, {
                    fields: {
                        "name[ar]": {
                            validators: {
                                notEmpty: {message: messages.required},
                                stringLength: {min: 3, max: 50, message: messages.name_length}
                            }
                        },
                        "name[en]": {
                            validators: {
                                notEmpty: {message: messages.required},
                                stringLength: {min: 3, max: 50, message: messages.name_length}
                            }
                        },

                        "currency_name[ar]": {
                            validators: {
                                notEmpty: {message: messages.required},
                                stringLength: {min: 3, max: 50, message: messages.name_length}
                            }
                        },
                        "currency_name[en]": {
                            validators: {
                                notEmpty: {message: messages.required},
                                stringLength: {min: 3, max: 50, message: messages.name_length}
                            }
                        },

                        "sign": {
                            validators: {
                                notEmpty: {message: messages.required},
                            }
                        },

                        "rate_per_point": {
                            validators: {
                                notEmpty: {message: messages.required},
                            }
                        },

                        "points_per_currency": {
                            validators: {
                                notEmpty: {message: messages.required},
                            }
                        },

                        "minimum_usable_points": {
                            validators: {
                                notEmpty: {message: messages.required},
                            }
                        },

                        "shipping_fee_near": {
                            validators: {
                                notEmpty: {message: messages.required},
                            }
                        },

                        "shipping_fee_far": {
                            validators: {
                                notEmpty: {message: messages.required},
                            }
                        },

                        "min_order_near": {
                            validators: {
                                notEmpty: {message: messages.required},
                            }
                        },

                        "min_order_far": {
                            validators: {
                                notEmpty: {message: messages.required},
                            }
                        },

                        "delivery_time": {
                            validators: {
                                notEmpty: {message: messages.required},
                            }
                        },
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap5: new FormValidation.plugins.Bootstrap5(),
                        submitButton: new FormValidation.plugins.SubmitButton(),
                        autoFocus: new FormValidation.plugins.AutoFocus(),
                        defaultSubmit: new FormValidation.plugins.DefaultSubmit()
                    }
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

            @if($type == 'cities')
            // تحميل الولايات عند اختيار الدولة
            function debounce(func, wait) {
                let timeout;
                return function (...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            const loadStates = debounce(function (countryId) {
                const statesSelect = $('#states');
                statesSelect.empty().append('<option value="">{{ __("admin.select_state") }}</option>');
                if (countryId) {
                    $.ajax({
                        url: '{{ route("locations.parents") }}',
                        method: 'GET',
                        data: {type: 'state', country_id: countryId},
                        success: function (response) {
                            response.forEach(function (state) {
                                statesSelect.append(`<option value="${state.id}">${state.name}</option>`);
                            });
                            statesSelect.trigger('change');
                        },
                        error: function (xhr) {
                            console.error('Error loading states:', xhr);
                            alert('{{ __("admin.error_loading_states") }}');
                        }
                    });
                }
            }, 300);

            $('#countries').on('change', function () {
                loadStates($(this).val());
            });

            @if(old('country_id'))
            loadStates('{{ old("country_id") }}');
            @endif
            @endif
        });
    </script>
@endsection
