@extends('dashboard.layout.master')
@section('title', __('admin.products') . ' - ' . $branch->name)

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <h5 class="card-header d-flex justify-content-between border-b">
                <div>
                    {{ __('admin.products') }} - {{ $branch->getTranslation('name', app()->getLocale()) }}
                </div>
                <div class="buttons d-flex justify-content-between">
                    @include('dashboard.partials.index.table_btns')
                    <a class="btn btn-secondary" href="{{ route('branches.index') }}">
                        <i class="menu-icon icon-base ti tabler-arrow-right"></i> {{ __('admin.back') }}
                    </a>
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
@endsection

@section('dashboard-footer')
    {{ $dataTable->scripts() }}
    @include('dashboard.partials.index.js')
    <script>
        $(document).on('click', '.toggle-status-btn', function() {
            const $btn = $(this);
            const productId = $btn.data('product-id');
            const branchId = {{ $branch->id }};
            const currentStatus = $btn.data('current-status');

            // Disable button during request
            $btn.prop('disabled', true);

            $.ajax({
                url: "{{ route('branches.products.toggle-status', ['id' => $branch->id, 'productId' => 0]) }}".replace('/0/toggle-status', '/' + productId + '/toggle-status'),
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
    </script>
@endsection
