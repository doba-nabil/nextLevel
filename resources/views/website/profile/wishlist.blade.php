@extends('website.layout.master')
@section('title',  __('website.favourite') )
@section('body', false)
@section('website-main')
    <!-- Content -->
    <div class="breadCrumb_section midPadding">
        <div class="container px-lg-0">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">{{ __('website.home') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('profile.index') }}">{{ __('website.my_account') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> {{ __('website.favourite') }}</li>
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
                    @include('website.profile.partials.wishlist')
                </div>
            </div>
        </div>
    </section>

    <!--/ Content -->
@endsection

@section('website-footer')
    <script>
        $(document).ready(function() {
            // Use event delegation for remove favorite button
            $(document).on('click', '.remove_favorite_btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var productId = $(this).data('product-id');
                var button = $(this);

                AppSwal.confirm({
                    title: '{{ __("website.are_you_sure_remove") }}',
                    text: '{{ __("website.product_removed_from_favorites") }}',
                    confirmButtonText: '{{ __("website.yes_remove") }}',
                    cancelButtonText: '{{ __("website.cancel") }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route("profile.remove.favorite") }}',
                            method: 'POST',
                            data: {
                                product_id: productId,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                button.closest('.col-12, .col-md-6, .col-lg-4').fadeOut(function() {
                                    $(this).remove();
                                });
                                AppSwal.success('{{ __("website.product_removed_from_favorites") }}', '{{ __("website.removed_from_favorites") }}');
                            },
                            error: function(xhr) {
                                AppSwal.error('{{ __("website.error_processing_favorite") }}');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
