@extends('dashboard.layout.master')
@section('title', 'Users - Edit')

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">Edit My Profile</h5>
                    <div class="card-body">
                        <form id="userForm" class="row g-6" method="POST" action="{{ route('profile.post') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control">
                                @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control">
                                @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
                                @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">

                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
                                <input type="password" name="password" class="form-control">
                                @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>

                            <div class="col-12">
                                <div class="dropzone needsclick" id="dropzone-basic">
                                    <div class="dz-message needsclick">
                                        Drop files here or click to upload
                                        <span class="note needsclick">
                (This is just a demo dropzone. Selected files are
                <span class="fw-medium">not</span> actually uploaded.)
            </span>
                                    </div>
                                </div>
                                @error("image")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-primary">Update</button>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const userForm = document.getElementById('userForm');
            if (userForm) {
                FormValidation.formValidation(userForm, {
                    fields: {
                        name: {
                            validators: {
                                notEmpty: {message: 'Name is required'},
                                stringLength: {min: 3, max: 50, message: 'Name must be between 3 and 50 characters'}
                            }
                        },
                        email: {
                            validators: {
                                notEmpty: {message: 'Email is required'},
                                emailAddress: {message: 'Email must be valid'}
                            }
                        },
                        password: {
                            validators: {
                                stringLength: {min: 6, message: 'Password must be at least 6 characters'}
                            }
                        },
                        password_confirmation: {
                            validators: {
                                identical: {
                                    compare: () => userForm.querySelector('[name="password"]').value,
                                    message: 'Password confirmation does not match'
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
            @if(isset($user) && $user->getFirstMediaUrl('users'))
            initImageCropper('#dropzone-basic', 'image', null, "{{ $user->getFirstMediaUrl('users') }}");
            @else
            initImageCropper('#dropzone-basic', 'image');
            @endif
        });
    </script>
@endsection
