@extends('website.layout.master')
@section('title',  __('website.my_wallet') )
@section('body', false)
@section('website-main')
    <!-- Content -->
    <div class="breadCrumb_section midPadding">
        <div class="container px-lg-0">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">{{ __('website.home') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('profile.index') }}">{{ __('website.my_account') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> {{ __('website.my_wallet') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="pickup_section secPadding pt-0">
        <div class="container container_start px-lg-0">
            <div class="row">
                <div class="col-12 col-lg-4">
                    @include('website.profile.partials.tabs')
                </div>
                <div class="col-12 col-lg-8">
                    @include('website.profile.partials.wallet')
                </div>
            </div>
        </div>
    </section>

    <!--/ Content -->
@endsection

@section('website-footer')
    <script>
        $(document).ready(function() {
            // Handle "Add Money" button click
            $('button[data-tab="add_money"]').on('click', function(e) {
                e.preventDefault();
                window.location.href = '{{ route('profile.wallet.add-money') }}';
            });
        });
    </script>
@endsection
