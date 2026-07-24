@extends('dashboard.layout.master')
@section('title', __('admin.products'))
@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Basic Bootstrap Table -->
        <button type="button" class="btn btn-success d-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#importProductsModal">
            <i class="menu-icon icon-base ti tabler-upload"></i>
            {{ __('admin.import_excel') }}
        </button>
        <br>
        <div class="card">


            <h5 class="card-header d-flex justify-content-between border-b">
                {{__('admin.products')}}
                <div class="buttons d-flex justify-content-between">
                    @include('dashboard.partials.index.table_btns')
                    <a class="btn btn-primary" href="{{ url('admin/products/create') }}"><i
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

    <!-- Modal استيراد المنتجات -->
    <div class="modal fade" id="importProductsModal" tabindex="-1" aria-labelledby="importProductsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">

                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="importProductsModalLabel">
                        <i class="ti ti-upload me-1"></i> {{ __('admin.import_products_excel') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label fw-bold">{{ __('admin.choose_excel_file') }}</label>
                            <input type="file" name="file" id="file" class="form-control" accept=".xlsx,.xls" required>
                            <small class="text-muted d-block mt-2">
                                ⚠️ {{ __('admin.allowed_file_types') }}: .xlsx, .xls
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            {{ __('admin.close') }}
                        </button>
                        <button type="submit" class="btn btn-success">
                            {{ __('admin.import') }}
                        </button>
                    </div>
                </form>

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

