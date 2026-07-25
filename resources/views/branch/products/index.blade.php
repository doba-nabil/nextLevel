@extends('branch.layouts.master')
@section('title', $title ?? __('admin.branch_offerings'))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <h5 class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-b">
                <div>
                    {{ $title ?? __('admin.branch_offerings') }}
                </div>
            </h5>
            <div class="table-responsive text-nowrap">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>
@endsection

@section('dashboard-head')
    @include('dashboard.partials.index.css')
    <script>window.branchProductTypeFilter = '{{ request()->input("product_type", "product") }}';</script>
@endsection

@section('dashboard-footer')
    {{ $dataTable->scripts() }}
    @include('dashboard.partials.index.js')
    <script>
        $(document).ready(function() {
            const tableSelector = '#branch-products-table';
            
            // Set initial active button based on current product type
            $('.branch-type-filter-btn').removeClass('btn-primary active').addClass('btn-outline-primary');
            $('.branch-type-filter-btn[data-type="' + window.branchProductTypeFilter + '"]').removeClass('btn-outline-primary').addClass('btn-primary active');

            $('.branch-type-filter-btn').on('click', function() {
                const type = $(this).data('type');
                window.branchProductTypeFilter = type;

                $('.branch-type-filter-btn')
                    .removeClass('btn-primary active')
                    .addClass('btn-outline-primary');
                $(this)
                    .removeClass('btn-outline-primary')
                    .addClass('btn-primary active');

                if ($.fn.DataTable.isDataTable(tableSelector)) {
                    // true = reset to page 1 when switching product / meal / box
                    $(tableSelector).DataTable().ajax.reload(null, true);
                }
            });
        });

        $(document).on('click', '.toggle-status-btn', function() {
            const $btn = $(this);
            const productId = $btn.data('product-id');
            const currentStatus = $btn.data('current-status');

            // Disable button during request
            $btn.prop('disabled', true);

            $.ajax({
                url: "{{ route('branch.products.toggle-status', ['productId' => 0]) }}".replace('/0/toggle-status', '/' + productId + '/toggle-status'),
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Reload DataTable
                        $('#branch-products-table').DataTable().ajax.reload(null, false);

                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __('admin.success') ?? 'نجح' }}',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __('admin.error') ?? 'خطأ' }}',
                            text: response.message || '{{ __('admin.something_went_wrong') ?? 'حدث خطأ' }}'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('admin.error') ?? 'خطأ' }}',
                        text: xhr.responseJSON?.message || '{{ __('admin.something_went_wrong') ?? 'حدث خطأ' }}'
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        $(document).on('click', '.save-product-settings-btn', function() {
            const $btn = $(this);
            const productId = $btn.data('product-id');

            const $row = $btn.closest('tr');
            if ($row.find('.inline-stock-input').length === 0) {
                return;
            }

            const trackStock = $row.find('.inline-track-stock-checkbox').is(':checked') ? 1 : 0;
            const stock = $row.find('.inline-stock-input').val();
            const maxOrderQty = $row.find('.inline-max-qty-input').val();
            const threshold = $row.find('.inline-threshold-input').val();

            // Disable button during request
            $btn.prop('disabled', true);

            $.ajax({
                url: "{{ route('branch.products.update-settings', ['productId' => 0]) }}".replace('/0/update-settings', '/' + productId + '/update-settings'),
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    track_stock: trackStock,
                    stock: stock,
                    max_order_quantity: maxOrderQty,
                    low_stock_threshold: threshold
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __('admin.success') ?? 'نجح' }}',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        // Reload data table in background to update any row status
                        $('#branch-products-table').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __('admin.error') ?? 'خطأ' }}',
                            text: response.message || '{{ __('admin.something_went_wrong') ?? 'حدث خطأ' }}'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('admin.error') ?? 'خطأ' }}',
                        text: xhr.responseJSON?.message || '{{ __('admin.something_went_wrong') ?? 'حدث خطأ' }}'
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });
    </script>
@endsection
