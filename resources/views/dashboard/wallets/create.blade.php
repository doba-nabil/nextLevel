@extends('dashboard.layout.master')
@section('title', __('admin.create') .' '. __('admin.wallets_transactions'))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ __('admin.create') .' '. __('admin.wallets_transactions') }}</h5>
                    <div class="card-body">
                        <form id="mealForm" class="row g-3" method="POST" action="{{ route('wallets.store') }}">
                            @csrf
                            <div class="col-md-6 mt-3">
                                <label for="types" class="form-label">{{ __('admin.type') }}</label>
                                <select id="types" name="type" class="form-select select2">
                                    <option
                                         {{ '' == old('type') ? 'selected' : '' }}>
                                        {{ __('admin.please_select') }}</option>
                                    <option
                                        value="deposit" {{ 'deposit' == old('type') ? 'selected' : '' }}>
                                        {{ __('admin.deposit') }}</option>
                                    <option
                                        value="withdraw" {{ 'withdraw' == old('type') ? 'selected' : '' }}>
                                        {{ __('admin.withdraw') }}</option>
                                </select>
                            </div>

                            <div class="col-md-6 mt-3">
                                <label for="users" class="form-label">{{ __('admin.customers') }}</label>
                                <select id="users" name="user_id" class="form-select select2">
                                    @foreach($users as $user)
                                        <option
                                            value="{{ $user->id }}" {{ $user->id == old('user_id') ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mt-3">
                                <label for="users" class="form-label">{{ __('admin.customers') }}</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" class="form-control" placeholder="Amount"
                                           name="amount"
                                           value="{{ old("amount") }}" aria-label="Amount">
                                </div>
                                @error("amount") <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.notes') }}</label>
                                <input type="text" name="notes" value="{{ old('notes') }}" class="form-control">
                                @error('notes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
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
                                notEmpty: {message: messages.name_required},
                                stringLength: {min: 3, max: 50, message: messages.name_length}
                            }
                        },
                        "name[en]": {
                            validators: {
                                notEmpty: {message: messages.name_required},
                                stringLength: {min: 3, max: 50, message: messages.name_length}
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
