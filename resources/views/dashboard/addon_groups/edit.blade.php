@extends('dashboard.layout.master')
@section('title', __('admin.edit') .' . '. $group->name)

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header"> {{ __('admin.edit') .'  '. $group->name }}</h5>
                    <div class="card-body">
                        <form id="formValidationExamples" class="row g-6" method="POST"
                              action="{{ route('addon_groups.update' , $group->id) }}">
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
                                                <input value="{{$group->getTranslation('name', $localeCode)}}" type="text" class="form-control" name="name[{{$localeCode}}]"
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

                            <div class="col-md-6" style="display: none;">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.max_items') }}</label>
                                    <input value="{{ $group->max_items }}" step="1" min="0" max="100"  type="text" class="form-control" name="max_items"
                                           placeholder="{{ __('admin.max_items') }}">
                                    @error("max_items")
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.status') }}</label>
                                    <select id="active" class="select2 form-control" name="active"
                                            style="width:100%;">
                                        <option {{ $group->active ? 'selected' : '' }} value="1">{{ __('admin.active') }}</option>
                                        <option {{ !$group->active ? 'selected' : '' }} value="0">{{ __('admin.deactive') }}</option>
                                    </select>
                                    @error('active')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_selection_mandatory" 
                                               name="is_selection_mandatory" value="1" {{ old('is_selection_mandatory', $group->is_selection_mandatory) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_selection_mandatory">
                                            {{ __('admin.selection_mandatory_for_customers') }}
                                        </label>
                                    </div>
                                    @error('is_selection_mandatory')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.maximum_number_of_options_customer_can_select') }}</label>
                                    <input value="{{ old("max_selections", $group->max_selections) }}" type="number" step="1" min="1" class="form-control" name="max_selections"
                                           placeholder="{{ __('admin.maximum_number_of_options_customer_can_select') }}">
                                    @error("max_selections")
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6" id="min_selections_row" style="display: {{ old('is_selection_mandatory', $group->is_selection_mandatory) ? 'block' : 'none' }};">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.minimum_number_of_selections') }}</label>
                                    <input value="{{ old("min_selections", $group->min_selections) }}" type="number" step="1" min="1" class="form-control" name="min_selections"
                                           placeholder="{{ __('admin.minimum_number_of_selections') }}">
                                    @error("min_selections")
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
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
    @include('dashboard.partials.edit.js')
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
            // Handle is_selection_mandatory checkbox toggle to show/hide min_selections field
            const mandatoryCheckbox = document.getElementById('is_selection_mandatory');
            const minSelectionsRow = document.getElementById('min_selections_row');
            
            if (mandatoryCheckbox && minSelectionsRow) {
                function toggleMinSelections() {
                    if (mandatoryCheckbox.checked) {
                        minSelectionsRow.style.display = 'block';
                    } else {
                        minSelectionsRow.style.display = 'none';
                        // Clear the value when hidden
                        const minSelectionsInput = minSelectionsRow.querySelector('input[name="min_selections"]');
                        if (minSelectionsInput) {
                            minSelectionsInput.value = '';
                        }
                    }
                }
                
                // Initial state
                toggleMinSelections();
                
                // Listen for changes
                mandatoryCheckbox.addEventListener('change', toggleMinSelections);
            }

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
