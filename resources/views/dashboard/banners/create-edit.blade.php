@extends('dashboard.layout.master')
@section('title', isset($model) ? 'Edit Banner' : 'Create Banner')

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ isset($model) ? 'Edit Banner' : 'Create Banner' }}</h5>
                    <div class="card-body">
                        <form id="formValidationExamples" class="row g-6" method="POST"
                              action="{{ isset($model) ? route('banners.update', $model->id) : route('banners.store') }}" enctype="multipart/form-data">
                            @csrf
                            @if(isset($model))
                                @method('PUT')
                            @endif

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.link') }}</label>
                                    <input value="{{ old("link", isset($model) ? $model->link : '') }}" type="url" class="form-control" name="link"
                                           placeholder="{{ __('admin.link') }}">
                                    @error("link")
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('admin.order') }}</label>
                                    <input value="{{ old("order", isset($model) ? $model->order : 0) }}" type="number" class="form-control" name="order"
                                           placeholder="0" min="0">
                                    @error("order")
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">{{ __('admin.status') }}</label>
                                        <select id="active" class="select2 form-control" name="active"
                                                style="width:100%;">
                                            <option value="1" {{ (isset($model) && $model->active) || old('active', '1') == '1' ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                                            <option value="0" {{ (isset($model) && !$model->active) || old('active') == '0' ? 'selected' : '' }}>{{ __('admin.deactive') }}</option>
                                        </select>
                                        @error('active')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mt-3">
                                <label class="form-label">{{ __('admin.arabic') }} {{ __('admin.image') }} <span class="text-danger">*</span> <span class="text-muted small">({{ __('admin.recommended') }}: 1920x600px)</span></label>
                                <div class="dropzone needsclick" id="dropzone-ar">
                                    <div class="dz-message needsclick">
                                        {{__('admin.Drop files here or click to upload')}}
                                    </div>
                                </div>
                                @error("image_ar")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mt-3">
                                <label class="form-label">{{ __('admin.english') }} {{ __('admin.image') }} <span class="text-danger">*</span> <span class="text-muted small">({{ __('admin.recommended') }}: 1920x600px)</span></label>
                                <div class="dropzone needsclick" id="dropzone-en">
                                    <div class="dz-message needsclick">
                                        {{__('admin.Drop files here or click to upload')}}
                                    </div>
                                </div>
                                @error("image_en")
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
    @if(isset($model))
        @include('dashboard.partials.edit.js')
    @else
        @include('dashboard.partials.create.js')
    @endif
    @php
        $messages = [
            'name_required' => __('admin.name_required'),
            'required' => __('admin.required'),
            'name_length' => __('admin.name_length'),
        ];
    @endphp
    <script>
        'use strict';

        document.addEventListener('DOMContentLoaded', function () {
            const bannerForm = document.getElementById('formValidationExamples');

            if (bannerForm) {
                const messages = @json($messages);

                const fv = FormValidation.formValidation(bannerForm, {
                    fields: {
                        "image_ar": {
                            validators: {
                                notEmpty: {message: messages.required},
                            }
                        },
                        "image_en": {
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
                    const firstInvalidField = bannerForm.querySelector('.is-invalid');

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
    @include('dashboard.partials.image-cropper-js')
    <script>
        'use strict';
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function() {
                // Initialize Arabic image dropzone
                const dropzoneArEl = document.querySelector('#dropzone-ar');
                if (dropzoneArEl && dropzoneArEl.dropzone) {
                    dropzoneArEl.dropzone.destroy();
                }
                const existingInstanceAr = Dropzone.instances.find(dz => dz.element && dz.element.id === 'dropzone-ar');
                if (existingInstanceAr) {
                    existingInstanceAr.destroy();
                }
                
                @if(isset($model) && $model->getFirstMediaUrl('banner_image_ar'))
                initImageCropper('#dropzone-ar', 'image_ar', null, "{{ $model->getFirstMediaUrl('banner_image_ar') }}");
                @else
                initImageCropper('#dropzone-ar', 'image_ar');
                @endif
                
                // Initialize English image dropzone
                const dropzoneEnEl = document.querySelector('#dropzone-en');
                if (dropzoneEnEl && dropzoneEnEl.dropzone) {
                    dropzoneEnEl.dropzone.destroy();
                }
                const existingInstanceEn = Dropzone.instances.find(dz => dz.element && dz.element.id === 'dropzone-en');
                if (existingInstanceEn) {
                    existingInstanceEn.destroy();
                }
                
                @if(isset($model) && $model->getFirstMediaUrl('banner_image_en'))
                initImageCropper('#dropzone-en', 'image_en', null, "{{ $model->getFirstMediaUrl('banner_image_en') }}");
                @else
                initImageCropper('#dropzone-en', 'image_en');
                @endif
            }, 100);
        });
    </script>
@endsection

