@extends('website.layout.master')
@section('title', '500 - ' . __('errors.error'))

@section('website-main')
<section class="error-page secPadding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 text-center">
                <div class="error-icon mb-4">
                    <i class="fa-solid fa-triangle-exclamation fa-8x" style="color: #f6d814;"></i>
                </div>
                <h1 class="error-code display-1 fw-bold mb-3" style="color: #434345;">500</h1>
                <h2 class="error-title mb-4" style="color: #434345;">{{ __('errors.server_error') }}</h2>
                <p class="error-message mb-5 text-muted">
                    {{ __('errors.server_error_desc') }}
                </p>
                <a href="{{ url('/') }}" class="main_bttn hvr-sweep-to-right d-inline-flex mx-auto">
                    {{ __('errors.back_to_home') }}
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
