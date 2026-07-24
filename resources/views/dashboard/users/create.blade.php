@extends('dashboard.layout.master')
@section('title', __('admin.create'). ' . ' . __('admin.users'))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ __('admin.create'). '  ' . __('admin.users') }}</h5>
                    <div class="card-body">
                        <form id="userForm" class="row g-6" method="POST" action="{{ route('users.store') }}"
                              enctype="multipart/form-data">
                            @csrf

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.name') }}</label>
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control">
                                @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.email') }}</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                                @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.points') }}</label>
                                <input type="number" min="0" step="0.1" name="points" value="0" class="form-control">
                                @error('points')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.phone') }}</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                                @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.password') }}</label>
                                <input type="password" name="password" class="form-control">
                                @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.confirm_password') }}</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.status') }}</label>
                                    <select id="stauts" class="select2 form-control" name="status"
                                            style="width:100%;">
                                        <option value="pending">{{ __('admin.pending') }}</option>
                                        <option value="active">{{ __('admin.active') }}</option>
                                        <option value="deactive">{{ __('admin.deactive') }}</option>
                                        <option value="blocked">{{ __('admin.blocked') }}</option>
                                    </select>
                                    @error('status')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ __('admin.image') }} <span class="text-muted small">({{ __('admin.recommended') }}: 200x200px)</span></label>
                                <div class="dropzone needsclick" id="dropzone-basic">
                                    <div class="dz-message needsclick">
                                        {{ __('admin.Drop files here or click to upload') }}
                                    </div>
                                </div>
                                @error("image")
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
        document.addEventListener('DOMContentLoaded', function () {
            const userForm = document.getElementById('userForm');
            if (userForm) {
                const messages = @json($messages);

                FormValidation.formValidation(userForm, {
                    fields: {
                        name: {
                            validators: {
                                notEmpty: {message: messages.name_required},
                                stringLength: {min: 3, max: 50, message: messages.name_length}
                            }
                        },
                        email: {
                            validators: {
                                notEmpty: {message: messages.email_required},
                                emailAddress: {message: messages.email_valid}
                            }
                        },
                        password: {
                            validators: {
                                notEmpty: {message: messages.password_required},
                                stringLength: {min: 6, message: messages.password_length}
                            }
                        },
                        password_confirmation: {
                            validators: {
                                identical: {
                                    compare: () => userForm.querySelector('[name="password"]').value,
                                    message: messages.password_confirm
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
            }
        });
    </script>
    @include('dashboard.partials.image-cropper-js')
    <script>
        'use strict';
        document.addEventListener('DOMContentLoaded', function () {
            initImageCropper('#dropzone-basic', 'image');
        });
    </script>
@endsection
