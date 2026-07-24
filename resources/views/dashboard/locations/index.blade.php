@extends('dashboard.layout.master')
@section('title', __('admin.'.$typee))
@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <h5 class="card-header d-flex justify-content-between border-b">
                {{__('admin.'.$typee)}}
                <div class="buttons d-flex justify-content-between">
                    @include('dashboard.partials.index.table_btns')
                    <a class="btn btn-primary" href="{{ url('admin/'.$url.'/create') }}"><i
                            class="menu-icon icon-base ti tabler-plus"></i> {{ __('admin.add_new') }}</a>
                </div>
            </h5>
            <div class="table-responsive text-nowrap">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>

    @if($type == 'state')
    {{-- Modal إعدادات توحيد المدن --}}
    <div class="modal fade" id="unifyCitiesModal" tabindex="-1" aria-labelledby="unifyCitiesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="unifyCitiesModalLabel">
                        {{ __('admin.unify_cities_settings') ?? 'Unify Cities Settings' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        {{ __('admin.unify_cities_hint') ?? 'Set these values to apply to all cities in this state' }}
                    </p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('admin.shipping_fee') . ' ' . __('admin.within_city') }}</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.1"
                                    class="form-control"
                                    id="unify_shipping_fee_near"
                                    placeholder="{{ __('admin.shipping_fee') . ' ' . __('admin.within_city') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('admin.shipping_fee') . ' ' . __('admin.outside_city') }}</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.1"
                                    class="form-control"
                                    id="unify_shipping_fee_far"
                                    placeholder="{{ __('admin.shipping_fee') . ' ' . __('admin.outside_city') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('admin.min_order') . ' ' . __('admin.within_city') }}</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.1"
                                    class="form-control"
                                    id="unify_min_order_near"
                                    placeholder="{{ __('admin.min_order') . ' ' . __('admin.within_city') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('admin.min_order') . ' ' . __('admin.outside_city') }}</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.1"
                                    class="form-control"
                                    id="unify_min_order_far"
                                    placeholder="{{ __('admin.min_order') . ' ' . __('admin.outside_city') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('admin.cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary" id="unifyCitiesBtn">
                        {{ __('admin.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection
@section('dashboard-head')
    @include('dashboard.partials.index.css')
@endsection

@section('dashboard-footer')
    {{ $dataTable->scripts() }}
    @include('dashboard.partials.index.js')
    
    @if($type == 'state')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentStateId = null;

            // Handle unify cities button click from dropdown
            $(document).on('click', '.unify-cities-btn', function(e) {
                e.preventDefault();
                currentStateId = $(this).data('state-id');
                // Clear form fields
                $('#unify_shipping_fee_near').val('');
                $('#unify_shipping_fee_far').val('');
                $('#unify_min_order_near').val('');
                $('#unify_min_order_far').val('');
            });

            // Handle save button in modal
            $('#unifyCitiesBtn').on('click', function() {
                if (!currentStateId) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("admin.error") }}',
                        text: '{{ __("admin.state_not_selected") ?? "State not selected" }}'
                    });
                    return;
                }

                const shippingFeeNear = $('#unify_shipping_fee_near').val() || '';
                const shippingFeeFar = $('#unify_shipping_fee_far').val() || '';
                const minOrderNear = $('#unify_min_order_near').val() || '';
                const minOrderFar = $('#unify_min_order_far').val() || '';

                // Check if at least one field is filled
                if (!shippingFeeNear && !shippingFeeFar && !minOrderNear && !minOrderFar) {
                    Swal.fire({
                        icon: 'warning',
                        title: '{{ __("admin.warning") }}',
                        text: '{{ __("admin.please_fill_at_least_one_field") ?? "Please fill at least one field" }}'
                    });
                    return;
                }

                Swal.fire({
                    title: '{{ __("admin.confirm") }}',
                    text: '{{ __("admin.unify_cities_confirm") ?? "Are you sure you want to update all cities in this state?" }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ __("admin.yes") ?? "Yes" }}',
                    cancelButtonText: '{{ __("admin.cancel") ?? "Cancel" }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: '{{ __("admin.processing") ?? "Processing" }}',
                            text: '{{ __("admin.please_wait") ?? "Please wait" }}',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: '{{ route("states.unify-cities", ":id") }}'.replace(':id', currentStateId),
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                shipping_fee_near: shippingFeeNear,
                                shipping_fee_far: shippingFeeFar,
                                min_order_near: minOrderNear,
                                min_order_far: minOrderFar
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '{{ __("admin.success") }}',
                                    text: response.message || '{{ __("admin.cities_unified_success") ?? "Cities updated successfully" }}'
                                }).then(() => {
                                    // Close modal
                                    $('#unifyCitiesModal').modal('hide');
                                    // Reload DataTable
                                    if ($.fn.DataTable) {
                                        $('#location-table').DataTable().ajax.reload();
                                    }
                                });
                            },
                            error: function(xhr) {
                                let errorMessage = '{{ __("admin.error") ?? "An error occurred" }}';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __("admin.error") }}',
                                    text: errorMessage
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endif
@endsection

