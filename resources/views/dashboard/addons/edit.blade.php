@extends('dashboard.layout.master')
@section('title', __('admin.edit') . '.' . $addon->name)

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ __('admin.edit') . '.' . $addon->name }}</h5>
                    <div class="card-body">
                        <form id="formValidationExamples" class="row g-6" method="POST"
                              action="{{ route('addons.update', $addon->id) }}">
                            @csrf
                            @method('PUT')

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
                                                <input value="{{ old("name.$localeCode", $addon->getTranslation('name', $localeCode)) }}"
                                                       type="text" class="form-control" name="name[{{$localeCode}}]"
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="addon_group_id" class="form-label">{{ __('admin.gourp') }}</label>
                                    <select id="addon_group_id" class="form-select select2" name="addon_group_id">
                                        <option value="">{{ __('admin.select_group') }}</option>
                                        @foreach ($addon_groups as $group)
                                            <option value="{{ $group->id }}"
                                                {{ old('addon_group_id', $addon->addon_group_id) == $group->id ? 'selected' : '' }}>
                                                {{ $group->getTranslation('name', app()->getLocale()) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('addon_group_id')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.status') }}</label>
                                    <select id="active" class="select2 form-control" name="active"
                                            style="width:100%;">
                                        <option {{ $addon->active ? 'selected' : '' }} value="1">{{ __('admin.active') }}</option>
                                        <option {{ !$addon->active ? 'selected' : '' }} value="0">{{ __('admin.deactive') }}</option>
                                    </select>
                                    @error('active')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <hr>
                            <h6 class="text-primary mb-1 mt-2">{{ __('admin.prices') }}</h6>
                            @foreach($currencies as $currency)
                                @php
                                    $price = $addon->prices->where('currency_id', $currency->id)->first();
                                @endphp
                                <div class="col-md-6 mt-3">
                                    <div class="input-group">
                                        <span class="input-group-text">{{ $currency->name }}</span>
                                        <input type="number" step="0.01" min="0" class="form-control"
                                               placeholder="Amount" name="prices[{{ $currency->id }}]"
                                               value="{{ old("prices[$currency->id]", $price?->price) }}"
                                               aria-label="Amount (to the nearest dollar)">
                                        <span class="input-group-text">{{ $currency->sign }}</span>
                                    </div>
                                    @error("prices.$currency->id") <span
                                        class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            @endforeach

                            {{-- زر الحفظ --}}
                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-primary">{{ __('admin.update') }}</button>
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

        document.addEventListener('DOMContentLoaded', function () {
            const categoryForm = document.getElementById('formValidationExamples');

            if (categoryForm) {
                const messages = @json($messages);
                const fv = FormValidation.formValidation(categoryForm, {
                    fields: {
                        "name[ar]": {
                            validators: {
                                notEmpty: {message: messages.required},
                                stringLength: {
                                    min: 3,
                                    max: 50,
                                    message: messages.name_length
                                }
                            }
                        },
                        "name[en]": {
                            validators: {
                                notEmpty: {message: messages.required},
                                stringLength: {
                                    min: 3,
                                    max: 50,
                                    message: messages.name_length
                                }
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
        });
    </script>
@endsection
