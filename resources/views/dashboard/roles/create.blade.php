@extends('dashboard.layout.master')
@section('title', __('admin.create') .' . '. __('admin.role'))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ __('admin.create') .' '. __('admin.role') }}</h5>
                    <div class="card-body">
                        <form id="formValidationExamples" class="row g-6" method="POST"
                              action="{{ route('roles.store') }}">
                            @csrf

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
                                                <input value="{{ old("display_name.$localeCode") }}" type="text" class="form-control" name="display_name[{{$localeCode}}]"
                                                       placeholder="{{ __('admin.name') }}">
                                                @error("display_name.$localeCode")
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="form-group">
                                <div class="d-flex justify-content-between">
                                    <label>Permissions</label>
                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                        <label class="form-check-label" for="select-all">Select All</label>
                                    </div>
                                </div>
                                <div class="row">
                                    @foreach($permissions as $group => $groupPermissions)
                                        <div class="card mt-4">
                                            <div class="card-header">
                                                <h6 class="text-success mb-0">{{ $groupPermissions->first()->group_name }}</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    @foreach($groupPermissions as $permission)
                                                        <div class="col-md-3">
                                                            <div class="form-check form-check-info mt-2">
                                                                <input class="form-check-input permission-checkbox"
                                                                       type="checkbox"
                                                                       value="{{ $permission->id }}"
                                                                       id="addon-{{ $permission->id }}"
                                                                       name="permissions[]">
                                                                <label class="form-check-label"
                                                                       for="addon-{{ $permission->id }}">
                                                                    {{ $permission->display_name }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
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
    <script>
        'use strict';

        document.addEventListener('DOMContentLoaded', function () {
            const categoryForm = document.getElementById('formValidationExamples');

            if (categoryForm) {
                const fv = FormValidation.formValidation(categoryForm, {
                    fields: {
                        "display_name[ar]": {
                            validators: {
                                notEmpty: {message: 'الاسم بالعربية مطلوب'},
                                stringLength: {
                                    min: 3,
                                    max: 50,
                                    message: 'الاسم يجب أن يكون بين 3 و 50 حرف'
                                }
                            }
                        },
                        "display_name[en]": {
                            validators: {
                                notEmpty: {message: 'Name in English is required'},
                                stringLength: {
                                    min: 3,
                                    max: 50,
                                    message: 'Name must be between 3 and 50 characters'
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
        document.addEventListener("DOMContentLoaded", function () {
            const selectAll = document.getElementById("select-all");
            const checkboxes = document.querySelectorAll(".permission-checkbox");

            selectAll.addEventListener("change", function () {
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
            checkboxes.forEach(cb => {
                cb.addEventListener("change", function () {
                    if (document.querySelectorAll(".permission-checkbox:checked").length === checkboxes.length) {
                        selectAll.checked = true;
                    } else {
                        selectAll.checked = false;
                    }
                });
            });
        });
    </script>
@endsection
