@extends('dashboard.layout.master')
@section('title', 'Users - Edit')

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ __('admin.edit') .' . ' . $user->name }}</h5>
                    <div class="card-body">
                        <form id="userForm" class="row g-6" method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.name') }}</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control">
                                @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.email') }}</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control">
                                @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.points') }}</label>
                                <div class="input-group">
                                    <input type="number" min="0" step="0.1" name="points" value="{{ old('points', $user->points) }}" class="form-control" id="user-points-input">
                                    @if($user->points > 0)
                                        <button type="button" class="btn btn-warning convert-points-btn" data-user-id="{{ $user->id }}" data-points="{{ $user->points }}">
                                            <i class="icon-base ti tabler-exchange"></i> {{ __('admin.convert_to_wallet') }}
                                        </button>
                                    @endif
                                </div>
                                @error('points')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.phone') }}</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
                                @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('admin.confirm_password') }} <small class="text-muted">(leave blank to keep current)</small></label>
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
                                        <option {{ $user->status == 'pending' ? 'selected' : '' }} value="pending">{{ __('admin.pending') }}</option>
                                        <option {{ $user->status == 'active'  ? 'selected' : '' }} value="active">{{ __('admin.active') }}</option>
                                        <option {{ $user->status == 'deactive' ? 'selected' : '' }} value="deactive">{{ __('admin.deactive') }}</option>
                                        <option {{ $user->status == 'blocked' ? 'selected' : '' }} value="blocked">{{ __('admin.blocked') }}</option>
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
    @include('dashboard.partials.edit.js')
    @php
        $messages = [
            'name_required' => __('admin.name_required'),
            'name_length' => __('admin.name_length'),
            'email_required' => __('admin.email_required'),
            'email_valid' => __('admin.email_valid'),
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
                                notEmpty: { message: messages.name_required },
                                stringLength: { min: 3, max: 50, message: messages.name_length }
                            }
                        },
                        email: {
                            validators: {
                                notEmpty: { message: messages.email_required },
                                emailAddress: { message: messages.email_valid }
                            }
                        },
                        password: {
                            validators: {
                                stringLength: { min: 6, message: messages.password_length }
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
            @if(isset($user) && $user->getFirstMediaUrl('users'))
            initImageCropper('#dropzone-basic', 'image', null, "{{ $user->getFirstMediaUrl('users') }}");
            @else
            initImageCropper('#dropzone-basic', 'image');
            @endif

            // Convert Points to Wallet Handler
            $(document).on('click', '.convert-points-btn', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const userId = $btn.data('user-id');
                const points = $btn.data('points');
                
                @php
                    $pointsPerKd = (float) \App\Models\Setting::getValue('points_per_kd', null, 100);
                    $convertedAmount = $user->points > 0 ? ($user->points / $pointsPerKd) : 0;
                @endphp
                
                Swal.fire({
                    title: '{{ __("admin.convert_points_confirmation") }}',
                    html: `<p class="mb-3">{{ __("admin.convert_points_confirmation_message") }}</p>
                           <p><strong>{{ __("admin.points") }}:</strong> ${points}</p>
                           <p><strong>{{ __("admin.amount") }}:</strong> {{ number_format($convertedAmount, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</p>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '{{ __("admin.yes_convert") }}',
                    cancelButtonText: '{{ __("admin.cancel") }}',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $btn.prop('disabled', true).html('<i class="icon-base ti tabler-loader"></i> {{ __("admin.processing") }}...');
                        
                        $.ajax({
                            url: '{{ route("users.convert-points", $user->id) }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '{{ __("admin.success") }}',
                                        text: response.message,
                                        confirmButtonText: '{{ __("admin.ok") }}'
                                    }).then(() => {
                                        // Update points input
                                        $('#user-points-input').val(0);
                                        // Remove button or update it
                                        $btn.remove();
                                        // Reload page to show updated wallet balance
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: '{{ __("admin.error") }}',
                                        text: response.message
                                    });
                                    $btn.prop('disabled', false).html('<i class="icon-base ti tabler-exchange"></i> {{ __("admin.convert_to_wallet") }}');
                                }
                            },
                            error: function(xhr) {
                                const message = xhr.responseJSON?.message || '{{ __("admin.points_conversion_failed") }}';
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __("admin.error") }}',
                                    text: message
                                });
                                $btn.prop('disabled', false).html('<i class="icon-base ti tabler-exchange"></i> {{ __("admin.convert_to_wallet") }}');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
