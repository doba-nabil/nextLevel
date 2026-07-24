@extends('dashboard.layout.master')
@section('title', __('admin.users'))
@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Basic Bootstrap Table -->
        <div class="card">
            <h5 class="card-header d-flex justify-content-between border-b">
                {{__('admin.users')}}
                <div class="buttons d-flex justify-content-between">
                    @include('dashboard.partials.index.table_btns')
                    <a class="btn btn-primary" href="{{ url('admin/users/create') }}"><i
                            class="menu-icon icon-base ti tabler-plus"></i> {{ __('admin.add_new') }}</a>
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
        $(document).ready(function() {
            // Convert Points to Wallet Handler (from table)
            $(document).on('click', '.convert-points-table-btn', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const userId = $btn.data('user-id');
                const points = $btn.data('points');
                const url = $btn.data('url');

                @php
                    $pointsPerKd = (float) \App\Models\Setting::getValue('points_per_kd', null, 100);
                @endphp

                const convertedAmount = (points / {{ $pointsPerKd }}).toFixed(3);

                Swal.fire({
                    title: '{{ __("admin.convert_points_confirmation") }}',
                    html: `<p class="mb-3">{{ __("admin.convert_points_confirmation_message") }}</p>
                           <p><strong>{{ __("admin.points") }}:</strong> ${points}</p>
                           <p><strong>{{ __("admin.amount") }}:</strong> ${convertedAmount} {{ \App\Models\Currency::getCurrentCurrencySign() }}</p>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '{{ __("admin.yes_convert") }}',
                    cancelButtonText: '{{ __("admin.cancel") }}',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $btn.prop('disabled', true).html('<i class="icon-base ti tabler-loader"></i> {{ __("admin.processing") }}...');

                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '{{ __("admin.success") }}',
                                        text: response.message,
                                        confirmButtonText: '{{ __("admin.ok") }}'
                                    }).then(() => {
                                        // Reload DataTable
                                        $('.table').DataTable().ajax.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: '{{ __("admin.error") }}',
                                        text: response.message
                                    });
                                    $btn.prop('disabled', false);
                                }
                            },
                            error: function(xhr) {
                                const message = xhr.responseJSON?.message || '{{ __("admin.points_conversion_failed") }}';
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __("admin.error") }}',
                                    text: message
                                });
                                $btn.prop('disabled', false);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection

