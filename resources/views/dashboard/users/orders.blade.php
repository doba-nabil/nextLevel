@extends('dashboard.layout.master')
@section('title', __('admin.orders') . ' - ' . $user->name)

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __('admin.orders') . ' - ' . $user->name }}</h5>
                        <div>
                            <a href="{{ route('users.index') }}" class="btn btn-label-secondary">
                                <i class="icon-base ti tabler-arrow-left"></i> {{ __('admin.back') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive text-nowrap">
                            {{ $dataTable->table() }}
                        </div>
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
    <script src="{{ asset('dashboard') }}/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
    <script>
        $(document).on('change', '.status-select', function() {
            var select = $(this);
            var status = select.val();
            var orderId = select.data('id');
            var url = select.data('url');
            
            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    status: status,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __("admin.Success") }}',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Reload DataTable (it will preserve current URL parameters)
                    if (window.LaravelDataTables && window.LaravelDataTables['orders-table']) {
                        window.LaravelDataTables['orders-table'].ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("admin.Error") }}',
                        text: '{{ __("admin.Error") }}'
                    });
                }
            });
        });
    </script>
@endsection
