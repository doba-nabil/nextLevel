@extends('dashboard.layout.master')
@section('title', __('admin.categories'))
@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Basic Bootstrap Table -->
        <div class="card">
            <h5 class="card-header d-flex justify-content-between border-b">
                {{__('admin.categories')}}
                <div class="buttons d-flex justify-content-between">
                    @include('dashboard.partials.index.table_btns')
                    <a class="btn btn-primary" href="{{ url('admin/categories/create') }}"><i
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
            // Handle status toggle switch
            $(document).on('change', '.status-toggle', function() {
                const $toggle = $(this);
                const categoryId = $toggle.data('id');
                const url = $toggle.data('url');
                const isChecked = $toggle.is(':checked');
                
                // Disable toggle during request
                $toggle.prop('disabled', true);
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        active: isChecked ? 1 : 0,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            // Success - toggle stays in new position
                            toastr.success(response.message);
                        } else {
                            // Revert toggle on error
                            $toggle.prop('checked', !isChecked);
                            toastr.error(response.message || '{{ __("admin.error_occurred") }}');
                        }
                    },
                    error: function(xhr) {
                        // Revert toggle on error
                        $toggle.prop('checked', !isChecked);
                        const message = xhr.responseJSON?.message || '{{ __("admin.error_occurred") }}';
                        toastr.error(message);
                    },
                    complete: function() {
                        // Re-enable toggle after request
                        $toggle.prop('disabled', false);
                    }
                });
            });

            // Handle products toggle slide
            $(document).on('click', '.toggle-products-btn', function() {
                const $btn = $(this);
                const categoryId = $btn.data('category-id');
                const url = $btn.data('toggle-url');
                const $icon = $btn.find('i');
                const rowId = 'products-row-' + categoryId;
                let $productsRow = $('#' + rowId);
                
                // If products row already exists, just toggle it
                if ($productsRow.length) {
                    $productsRow.slideToggle();
                    $icon.toggleClass('tabler-chevron-down tabler-chevron-up');
                    return;
                }
                
                // Show loading
                $btn.prop('disabled', true);
                $icon.removeClass('tabler-chevron-down').addClass('tabler-loader tabler-loader-2');
                
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        if (response.status === 'success' && response.products) {
                            const products = response.products;
                            let productsHtml = '';
                            
                            if (products.length === 0) {
                                productsHtml = '<tr id="' + rowId + '"><td colspan="6" class="text-center text-muted py-4">{{ __("admin.no_products") }}</td></tr>';
                            } else {
                                productsHtml = '<tr id="' + rowId + '"><td colspan="6"><div class="p-3 bg-light rounded"><h6 class="mb-3">{{ __("admin.products") }} (' + products.length + ')</h6><div class="row g-2">';
                                
                                products.forEach(function(product) {
                                    const statusBadge = product.active 
                                        ? '<span class="badge bg-label-success">{{ __("admin.active") }}</span>'
                                        : '<span class="badge bg-label-secondary">{{ __("admin.deactive") }}</span>';
                                    
                                    const returnUrl = encodeURIComponent(window.location.href);
                                    const editUrl = product.edit_url + '?return_to=' + returnUrl;
                                    productsHtml += '<div class="col-md-4 mb-2"><div class="card p-2"><div class="d-flex justify-content-between align-items-center"><span>' + product.name + '</span><div>' + statusBadge + ' <a href="' + editUrl + '" class="btn btn-sm btn-link p-0 ms-2"><i class="icon-base ti tabler-edit"></i></a></div></div></div></div>';
                                });
                                
                                productsHtml += '</div></div></td></tr>';
                            }
                            
                            // Insert after the category row
                            const $categoryRow = $btn.closest('tr');
                            $(productsHtml).insertAfter($categoryRow).hide().slideDown();
                            $icon.removeClass('tabler-loader tabler-loader-2').addClass('tabler-chevron-up');
                        } else {
                            toastr.error('{{ __("admin.error_loading_products") }}');
                            $icon.removeClass('tabler-loader tabler-loader-2').addClass('tabler-chevron-down');
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || '{{ __("admin.error_loading_products") }}';
                        toastr.error(message);
                        $icon.removeClass('tabler-loader tabler-loader-2').addClass('tabler-chevron-down');
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
    
    <style>
        .status-toggle {
            cursor: pointer;
        }
        .toggle-products-btn {
            transition: transform 0.2s;
        }
        .toggle-products-btn:hover {
            transform: scale(1.2);
        }
        #category-table tbody tr[id^="products-row-"] {
            background-color: #f8f9fa;
        }
        #category-table tbody tr[id^="products-row-"] td {
            border-top: 0;
        }
    </style>
@endsection

