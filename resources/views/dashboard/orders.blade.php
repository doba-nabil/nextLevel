@extends('dashboard.layout.master')
@section('title', __('admin.orders'))
@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Basic Bootstrap Table -->
        <div class="card">
            <h5 class="card-header d-flex justify-content-between border-b">
                @if(isset($type) && $type !== 'all')
                    {{__('admin.' . $type)}}
                @else
                    {{__('admin.orders')}}
                @endif
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
                    window.LaravelDataTables['orders-table'].ajax.reload(null, false);
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

