@extends('dashboard.layout.master')
@section('title', __('admin.meals'))
@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Basic Bootstrap Table -->
        <div class="card">
            <h5 class="card-header d-flex justify-content-between border-b">
                {{__('admin.meals')}}
                <div class="buttons d-flex justify-content-between">
                    @include('dashboard.partials.index.table_btns')
                    <a class="btn btn-info me-2" href="{{ route('meals.categories.order') }}"><i
                            class="menu-icon icon-base ti tabler-arrows-sort"></i> {{ __('admin.order_meals') }}</a>
                    <a class="btn btn-primary" href="{{ url('admin/meals/create') }}"><i
                            class="menu-icon icon-base ti tabler-plus"></i> {{ __('admin.add_new') }}</a>
                </div>
            </h5>
            <div class="table-responsive text-nowrap">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>

    <div class="modal fade" id="pricesModal" tabindex="-1" aria-labelledby="pricesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pricesModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="prices-content">
                        <p class="text-muted">
                            <i class="fa-solid fa-spinner"></i>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notesModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="notes-content">
                        <p class="text-muted">
                            <i class="fa-solid fa-spinner"></i>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('dashboard-head')
    @include('dashboard.partials.index.css')
@endsection

@section('dashboard-footer')
    {{ $dataTable->scripts() }}
    @include('dashboard.partials.index.js')
    <script>
        $(document).on('click', '.view-prices', function () {
            let productId = $(this).data('id');
            let productName = $(this).data('name');
            $('#pricesModalLabel').text("{{ __('admin.prices') }}: " + productName);
            $('#prices-content').html('<p class="text-muted"><i class="fa-solid fa-spinner"></i></p>');
            $('#pricesModal').modal('show');
            $.ajax({
                url: '{{ route("products.prices", ":id") }}'.replace(':id', productId),
                method: 'GET',
                success: function (response) {
                    $('#prices-content').html(response);
                },
                error: function () {
                    $('#prices-content').html('<p class="text-danger">{{ __("admin.error") }}</p>');
                }
            });
        });

        $(document).on('click', '.view-notes', function () {
            let productId = $(this).data('id');
            let productName = $(this).data('name');
            $('#notesModalLabel').text("{{ __('admin.product_notes') ?? 'Product Notes' }}: " + productName);
            $('#notes-content').html('<p class="text-muted"><i class="fa-solid fa-spinner"></i></p>');
            $('#notesModal').modal('show');
            $.ajax({
                url: '{{ route("products.notes", ":id") }}'.replace(':id', productId),
                method: 'GET',
                success: function (response) {
                    $('#notes-content').html(response);
                },
                error: function () {
                    $('#notes-content').html('<p class="text-danger">{{ __("admin.error") }}</p>');
                }
            });
        });
    </script>
@endsection

