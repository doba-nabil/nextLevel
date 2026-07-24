@extends('dashboard.layout.master')
@section('title', __('admin.home_page_settings'))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ __('admin.home_page_settings') }}</h5>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form id="homePageSettingsForm" action="{{ route('home-page-settings.post') }}" method="POST"
                              enctype="multipart/form-data">
                            @csrf

                            <hr class="my-4">

                            <!-- Banner Section Visibility -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="show_banner_section" 
                                               value="1" id="showBannerSection"
                                               {{ old('show_banner_section', $showBannerSection) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="showBannerSection">
                                            {{ __('admin.show_banner_section') }}
                                        </label>
                                    </div>
                                    <small class="text-muted">{{ __('admin.show_banner_section_description') }}</small>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="mb-3">{{ __('admin.sections_visibility') }}</h6>
                            
                            <!-- Slider Section Visibility -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="show_slider_section_desktop" 
                                               value="1" id="showSliderSectionDesktop"
                                               {{ old('show_slider_section_desktop', $showSliderSectionDesktop ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="showSliderSectionDesktop">
                                            {{ __('admin.show_slider_section_desktop') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="show_slider_section_mobile" 
                                               value="1" id="showSliderSectionMobile"
                                               {{ old('show_slider_section_mobile', $showSliderSectionMobile ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="showSliderSectionMobile">
                                            {{ __('admin.show_slider_section_mobile') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <small class="text-muted">{{ __('admin.show_slider_section_description') }}</small>
                                </div>
                            </div>

                            <div class="col-12 text-end mt-4">
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
@endsection

