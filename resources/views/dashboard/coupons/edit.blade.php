@extends('dashboard.layout.master')
@section('title', __('admin.edit') .' ' .$coupon->code)

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ __('admin.edit') .' '. $coupon->code }}</h5>
                    <div class="card-body">
                        <form id="mealForm" class="row g-3" method="POST" action="{{ route('coupons.update' , $coupon->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="col-md-6 mt-3">
                                <label for="types" class="form-label">{{ __('admin.type') }}</label>
                                <select id="types" name="type" class="form-select select2">
                                    <option
                                        value="percent" {{ 'percent' == $coupon->type ? 'selected' : '' }}>
                                        {{ __('admin.percent') }}</option>
                                    <option
                                        value="fixed" {{ 'fixed' == $coupon->type ? 'selected' : '' }}>
                                        {{ __('admin.fixed') }}</option>
                                </select>
                            </div>

                            <div class="col-md-6 mt-3">
                                <label for="users" class="form-label">{{ __('admin.customers') .' ('. __('admin.optional').')' }}</label>
                                <select id="users" name="user_id" class="form-select select2">
                                    <option value=" "
                                        {{ null == $coupon->user_id ? 'selected' : '' }}>
                                        {{ __('admin.all_users') }}</option>
                                    @foreach($users as $user)

                                        <option
                                            value="{{ $user->id }}" {{ $user->id == $coupon->user_id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mt-3">
                                <label for="users" class="form-label">{{ __('admin.amount') }}</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" class="form-control" placeholder="Amount"
                                           name="value"
                                           value="{{ $coupon->value }}" aria-label="Amount">
                                </div>
                                @error("value") <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.code') }}</label>
                                <input type="text" name="code" value="{{ $coupon->code }}" class="form-control">
                                @error('code')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.usage_limit') }}</label>
                                <input type="number" min="1" step="1" name="usage_limit" value="{{ $coupon->usage_limit }}" class="form-control">
                                @error('usage_limit')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.min_order_price') }}</label>
                                <input type="number" min="1" step="1" name="min_order_price" value="{{ $coupon->min_order_price }}" class="form-control">
                                @error('min_order_price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.expire_at') }}</label>
                                <input type="date" name="expire_at" value="{{ $coupon->expire_at }}" class="form-control">
                                @error('expire_at')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.status') }}</label>
                                    <select id="active" class="select2 form-control" name="active"
                                            style="width:100%;">
                                        <option {{ $coupon->active ? 'selected' : '' }} value="1">{{ __('admin.active') }}</option>
                                        <option {{ !$coupon->active ? 'selected' : '' }} value="0">{{ __('admin.deactive') }}</option>
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
