@extends('dashboard.layout.master')
@section('title', isset($model) ? 'Edit Slider' : 'Create Slider')

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ isset($model) ? 'Edit Slider' : 'Create Slider' }}</h5>
                    <div class="card-body">
                        <form id="formValidationExamples" class="row g-6" method="POST"
                              action="{{ isset($model) ? route('sliders.update', $model->id) : route('sliders.store') }}" enctype="multipart/form-data">
                            @csrf
                            @if(isset($model))
                                @method('PUT')
                            @endif

                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.link') }}</label>
                                <input value="{{ old("url", isset($model) ? $model->url : '') }}" type="url" class="form-control" name="url"
                                       placeholder="{{ __('admin.url') }}">
                                @error("url")
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.status') }}</label>
                                    <select id="active" class="select2 form-control" name="active"
                                            style="width:100%;">
                                        <option value="1" {{ isset($model) && $model->active ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                                        <option value="0" {{ isset($model) && !$model->active ? 'selected' : '' }}>{{ __('admin.deactive') }}</option>
                                    </select>
                                    @error('active')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mt-4">
                                <label class="form-label">{{ __('admin.image') }} ({{ __('admin.ar') }}) <span class="text-muted small">({{ __('admin.recommended') }}: 1920x600px)</span></label>
                                <div class="dropzone needsclick" id="dropzone-ar">
                                    <div class="dz-message needsclick">
                                        {{__('admin.Drop files here or click to upload')}}
                                    </div>
                                </div>
                                @error("image_ar")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mt-4">
                                <label class="form-label">{{ __('admin.image') }} ({{ __('admin.en') }}) <span class="text-muted small">({{ __('admin.recommended') }}: 1920x600px)</span></label>
                                <div class="dropzone needsclick" id="dropzone-en">
                                    <div class="dz-message needsclick">
                                        {{__('admin.Drop files here or click to upload')}}
                                    </div>
                                </div>
                                @error("image_en")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Legacy Image Handling: Pass it to the dropzone if no specific image exists -->
                            @php
                                $legacyImage = isset($model) && $model->getFirstMediaUrl('sliders') && !$model->getFirstMediaUrl('slider_image_ar') && !$model->getFirstMediaUrl('slider_image_en') ? $model->getFirstMediaUrl('sliders') : null;
                            @endphp

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
    @include('dashboard.partials.image-cropper-js')
    <script>
        'use strict';
        document.addEventListener('DOMContentLoaded', function () {
            // Pass legacy image from PHP to JS
            const legacyImage = "{{ $legacyImage ?? '' }}";

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

                @if(isset($model) && $model->getFirstMediaUrl('slider_image_ar'))
                initImageCropper('#dropzone-ar', 'image_ar', null, "{{ $model->getFirstMediaUrl('slider_image_ar') }}");
                @else
                // Use legacy image for Arabic if no specific image
                if (legacyImage) {
                    initImageCropper('#dropzone-ar', 'image_ar', null, legacyImage);
                } else {
                    initImageCropper('#dropzone-ar', 'image_ar');
                }
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

                @if(isset($model) && $model->getFirstMediaUrl('slider_image_en'))
                initImageCropper('#dropzone-en', 'image_en', null, "{{ $model->getFirstMediaUrl('slider_image_en') }}");
                @else
                // Use legacy image for English if no specific image
                if (legacyImage) {
                    initImageCropper('#dropzone-en', 'image_en', null, legacyImage);
                } else {
                    initImageCropper('#dropzone-en', 'image_en');
                }
                @endif
            }, 100);
        });
    </script>
@endsection
