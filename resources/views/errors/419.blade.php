@extends('website.layout.master')
@section('title', '419 - ' . __('errors.error'))

@section('website-main')
<section class="error-page secPadding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 text-center">
                <div class="error-icon mb-4">
                    <i class="fa-solid fa-hourglass-end fa-8x" style="color: #f6d814;"></i>
                </div>
                <h1 class="error-code display-1 fw-bold mb-3" style="color: #434345;">419</h1>
                <h2 class="error-title mb-4" style="color: #434345;">{{ __('errors.page_expired') }}</h2>
                <p class="error-message mb-5 text-muted">
                    {{ __('errors.page_expired_desc') }}
                </p>
                <a href="{{ url('/') }}" class="main_bttn hvr-sweep-to-right d-inline-flex mx-auto">
                    {{ __('errors.back_to_home') }}
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
