@extends('dashboard.layout.master')
@section('title', __('admin.site_settings'))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ __('admin.site_settings') }}</h5>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form id="settingsForm" action="{{ route('settings.post') }}" method="POST"
                              enctype="multipart/form-data">
                            @csrf

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.site_name_arabic') }}</label>
                                    <input type="text" name="site_name[ar]" class="form-control"
                                           value="{{ old('site_name.ar', \App\Models\Setting::getValue('site_name', 'ar')) }}">
                                    @error('site_name.ar')
                                    <div class="text-danger">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.site_name_english') }}</label>
                                    <input type="text" name="site_name[en]" class="form-control"
                                           value="{{ old('site_name.en', \App\Models\Setting::getValue('site_name', 'en')) }}">
                                    @error('site_name.en')
                                    <div class="text-danger">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.meta_title') }}</label>
                                    <input type="text" name="meta_title" class="form-control"
                                           value="{{ old('meta_title', \App\Models\Setting::getValue('meta_title')) }}">
                                    @error('meta_title')
                                    <div class="text-danger">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">{{ __('admin.meta_description') }}</label>
                                    <textarea name="meta_description" class="form-control"
                                              rows="3">{{ old('meta_description', \App\Models\Setting::getValue('meta_description')) }}</textarea>
                                    @error('meta_description')
                                    <div class="text-danger">{{ $message }}</div>@enderror
                                </div>


                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.contact_email') }}</label>
                                    <input type="email" name="contact_email" class="form-control"
                                           value="{{ old('contact_email', $settings['contact_email'] ?? '') }}">
                                    @error('contact_email')
                                    <div class="text-danger">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.contact_phone') }}</label>
                                    <input type="text" name="contact_phone" class="form-control"
                                           value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}">
                                    @error('contact_phone')
                                    <div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">{{ __('admin.logo_favicon') }}</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.logo') }} <span class="text-muted small">({{ __('admin.recommended') }}: 300x100px)</span></label>
                                    <div class="dropzone needsclick" id="dropzone-basic">
                                        <div class="dz-message needsclick">
                                            {{ __('admin.drop_files_here_or_click_to_upload') }}
                                        </div>
                                    </div>
                                    @error('logo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.favicon') }} <span class="text-muted small">({{ __('admin.recommended') }}: 32x32px or 64x64px)</span></label>
                                    <div class="dropzone needsclick" id="dropzone-basicc">
                                        <div class="dz-message needsclick">
                                            {{ __('admin.drop_files_here_or_click_to_upload') }}
                                        </div>
                                    </div>
                                    @error('logo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">{{ __('admin.whatsapp_business_api') }}</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">{{ __('admin.whatsapp_api_key') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="whatsapp_api_key" class="form-control"
                                           value="{{ old('whatsapp_api_key', $settings['whatsapp_api_key'] ?? \App\Models\Setting::getValue('whatsapp_api_key', null, '')) }}"
                                           placeholder="{{ __('admin.whatsapp_api_key_placeholder') }}">
                                    <small class="text-muted">{{ __('admin.whatsapp_api_key_hint') }}</small>
                                    @error('whatsapp_api_key')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">{{ __('admin.whatsapp_phone_number_id') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="whatsapp_phone_number_id" class="form-control"
                                           value="{{ old('whatsapp_phone_number_id', $settings['whatsapp_phone_number_id'] ?? \App\Models\Setting::getValue('whatsapp_phone_number_id', null, '')) }}"
                                           placeholder="{{ __('admin.whatsapp_phone_number_id_placeholder') }}">
                                    <small class="text-muted">{{ __('admin.whatsapp_phone_number_id_hint') }}</small>
                                    @error('whatsapp_phone_number_id')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">{{ __('admin.social_media_pixel_title') }}</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">{{ __('admin.social_media_pixel') }}</label>
                                    <textarea name="social_media_pixel" class="form-control" rows="8"
                                              placeholder="{{ __('admin.social_media_pixel_placeholder') }}">{{ old('social_media_pixel', $settings['social_media_pixel'] ?? \App\Models\Setting::getValue('social_media_pixel', null, '')) }}</textarea>
                                    <small class="text-muted">{{ __('admin.social_media_pixel_hint') }}</small>
                                    @error('social_media_pixel')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">{{ __('admin.social_media') }}</h6>
                            <div id="socialsWrapper">
                                @foreach($socials as $index => $social)
                                    <div class="row g-3 mb-2 social-row">
                                        <div class="col-md-4">
                                            <input type="text" name="socials[{{ $index }}][name]" class="form-control"
                                                   placeholder="{{ __('admin.social_name') }}" value="{{ $social['name'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="url" name="socials[{{ $index }}][url]" class="form-control"
                                                   placeholder="{{ __('admin.social_url') }}" value="{{ $social['url'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="socials[{{ $index }}][icon]" class="form-control"
                                                   placeholder="{{ __('admin.social_icon_placeholder') }}"
                                                   value="{{ $social['icon'] ?? '' }}">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-danger remove-social">X</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-secondary mb-3" id="addSocial">{{ __('admin.add_social') }}</button>

                            <hr>
                            <h6 class="mb-3">{{ __('admin.product_notes_settings') }}</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="enable_product_notes" id="enable_product_notes"
                                               value="1" {{ \App\Models\Setting::getValue('enable_product_notes', null, '0') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_product_notes">
                                            {{ __('admin.enable_product_notes') }}
                                        </label>
                                        <small class="text-muted d-block mt-1">{{ __('admin.enable_product_notes_hint') }}</small>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">{{ __('admin.points_settings') }}</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.points_per_order_value') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="points_per_order_value" class="form-control" min="0" step="0.1"
                                           value="{{ old('points_per_order_value', \App\Models\Setting::getValue('points_per_order_value', null, 10)) }}"
                                           placeholder="{{ __('admin.points_per_order_value_placeholder') }}">
                                    <small class="text-muted d-block mt-1">{{ __('admin.points_per_order_value_hint') }}</small>
                                    @error('points_per_order_value')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">{{ __('admin.points_to_wallet_conversion_settings') }}</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.points_per_kwd') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="points_per_kd" class="form-control" min="1" step="1"
                                           value="{{ old('points_per_kd', \App\Models\Setting::getValue('points_per_kd', null, 100)) }}"
                                           placeholder="{{ __('admin.points_per_kwd_placeholder') }}">
                                    <small class="text-muted d-block mt-1">{{ __('admin.points_per_kwd_hint') }}</small>
                                    @error('points_per_kd')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.minimum_points_to_convert') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="minimum_points_to_convert" class="form-control" min="1" step="1"
                                           value="{{ old('minimum_points_to_convert', \App\Models\Setting::getValue('minimum_points_to_convert', null, 100)) }}"
                                           placeholder="{{ __('admin.minimum_points_to_convert_placeholder') }}">
                                    <small class="text-muted d-block mt-1">{{ __('admin.minimum_points_to_convert_hint') }}</small>
                                    @error('minimum_points_to_convert')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">{{ __('admin.download_section') }}</h6>

                            <!-- Download Section - Language Tabs -->
                            <ul class="nav nav-tabs" id="downloadLangTabs" role="tablist">
                                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                                id="download-{{$localeCode}}-tab" data-bs-toggle="tab"
                                                data-bs-target="#download-{{$localeCode}}" type="button" role="tab">
                                            {{ __('admin.'.$properties['name']) }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content mt-3 p-3" id="downloadLangTabsContent">
                                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                         id="download-{{$localeCode}}" role="tabpanel">
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-12">
                                                <label class="form-label">{{ __('admin.download_title') }}</label>
                                                <input type="text" name="dowonload_title[{{$localeCode}}]" class="form-control"
                                                       value="{{ old("dowonload_title.$localeCode", \App\Models\Setting::getValue('dowonload_title', $localeCode, '')) }}">
                                                @error("dowonload_title.$localeCode")
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label">{{ __('admin.download_content') }}</label>
                                                <textarea rows="4" class="form-control" name="dowonload_content[{{$localeCode}}]"
                                                          placeholder="{{ __('admin.download_content') }}">{{ old("dowonload_content.$localeCode", \App\Models\Setting::getValue('dowonload_content', $localeCode, '')) }}</textarea>
                                                @error("dowonload_content.$localeCode")
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Download URLs -->
                            <div class="row g-3 mb-3 mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.ios_url') }}</label>
                                    <input type="url" name="ios_url" class="form-control"
                                           value="{{ old('ios_url', \App\Models\Setting::getValue('ios_url', null, '')) }}"
                                           placeholder="{{ __('admin.ios_url_placeholder') }}">
                                    @error('ios_url')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.android_url') }}</label>
                                    <input type="url" name="android_url" class="form-control"
                                           value="{{ old('android_url', \App\Models\Setting::getValue('android_url', null, '')) }}"
                                           placeholder="{{ __('admin.android_url_placeholder') }}">
                                    @error('android_url')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">{{ __('admin.save_settings') }}</button>
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
    @include('dashboard.partials.image-cropper-js')
    <script>
        'use strict';
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Dropzones with cropper
            @if(isset($logoUrl) && $logoUrl)
            initImageCropper('#dropzone-basic', 'logo', null, "{{ $logoUrl }}");
            @else
            initImageCropper('#dropzone-basic', 'logo');
            @endif

            @if(isset($faviconUrl) && $faviconUrl)
            initImageCropper('#dropzone-basicc', 'favicon', null, "{{ $faviconUrl }}");
            @else
            initImageCropper('#dropzone-basicc', 'favicon');
            @endif

            // Social Media dynamic rows
            let socialIndex = {{ count($socials) }};
            document.getElementById('addSocial').addEventListener('click', function () {
                const wrapper = document.getElementById('socialsWrapper');
                const div = document.createElement('div');
                div.classList.add('row', 'g-3', 'mb-2', 'social-row');
                div.innerHTML = `
            <div class="col-md-4">
                <input type="text" name="socials[${socialIndex}][name]" class="form-control" placeholder="{{ __('admin.social_name') }}">
            </div>
            <div class="col-md-4">
                <input type="url" name="socials[${socialIndex}][url]" class="form-control" placeholder="{{ __('admin.social_url') }}">
            </div>
            <div class="col-md-3">
                <input type="text" name="socials[${socialIndex}][icon]" class="form-control" placeholder="{{ __('admin.social_icon_placeholder') }}">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger remove-social">X</button>
            </div>
        `;
                wrapper.appendChild(div);
                socialIndex++;
            });

            document.addEventListener('click', function (e) {
                if(e.target.classList.contains('remove-social')){
                    e.target.closest('.social-row').remove();
                }
            });

        });
    </script>
@endsection

