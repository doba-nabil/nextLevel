@extends('dashboard.layout.master')
@section('title', __('admin.menu_categories') . ' - ' . $menu->name)

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('menus.index') }}" class="btn btn-sm btn-secondary me-2">
                                <i class="icon-base ti tabler-arrow-left"></i> {{ __('admin.back') }}
                            </a>
                            <span>{{ __('admin.menu_categories') }} - {{ $menu->name }}</span>
                        </div>
                        <button type="button" class="btn btn-primary" id="saveOrderBtn" style="display: none;">
                            <i class="icon-base ti tabler-device-floppy"></i> {{ __('admin.save_order') }}
                        </button>
                    </h5>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(count($categoriesWithProducts) > 0 || count($productsWithoutCategory) > 0)
                            @foreach($categoriesWithProducts as $categoryData)
                                <div class="category-section mb-4">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="icon-base ti tabler-category me-2"></i>
                                                {{ $categoryData['category']->name }}
                                                <span class="badge bg-primary ms-2">{{ count($categoryData['products']) }} {{ __('admin.products') }}</span>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group sortable-products" data-category-id="{{ $categoryData['category']->id }}">
                                                @foreach($categoryData['products'] as $menuProduct)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center product-item" 
                                                        data-menu-product-id="{{ $menuProduct->id }}"
                                                        data-order="{{ $menuProduct->order }}">
                                                        <div class="d-flex align-items-center">
                                                            <i class="icon-base ti tabler-grip-vertical me-2 text-muted" style="cursor: move;"></i>
                                                            @if($menuProduct->product)
                                                                @php
                                                                    $imageUrl = $menuProduct->product->getFirstMediaUrl('products') ?: asset('website/assets/img/th.png');
                                                                @endphp
                                                                <img src="{{ $imageUrl }}" 
                                                                     alt="{{ $menuProduct->product->name }}" 
                                                                     class="me-3" 
                                                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                                <div>
                                                                    <strong>{{ $menuProduct->product->name }}</strong>
                                                                    <br>
                                                                    <small class="text-muted">{{ __('admin.order') }}: {{ $menuProduct->order }}</small>
                                                                </div>
                                                            @else
                                                                <span class="text-muted">{{ __('admin.product_deleted') }}</span>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <span class="badge bg-info me-2">{{ __('admin.order') }}: <span class="order-value">{{ $menuProduct->order }}</span></span>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @if(count($productsWithoutCategory) > 0)
                                <div class="category-section mb-4">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="icon-base ti tabler-category me-2"></i>
                                                {{ __('admin.without_category') }}
                                                <span class="badge bg-secondary ms-2">{{ count($productsWithoutCategory) }} {{ __('admin.products') }}</span>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group sortable-products" data-category-id="null">
                                                @foreach($productsWithoutCategory as $menuProduct)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center product-item" 
                                                        data-menu-product-id="{{ $menuProduct->id }}"
                                                        data-order="{{ $menuProduct->order }}">
                                                        <div class="d-flex align-items-center">
                                                            <i class="icon-base ti tabler-grip-vertical me-2 text-muted" style="cursor: move;"></i>
                                                            @if($menuProduct->product)
                                                                @php
                                                                    $imageUrl = $menuProduct->product->getFirstMediaUrl('products') ?: asset('website/assets/img/th.png');
                                                                @endphp
                                                                <img src="{{ $imageUrl }}" 
                                                                     alt="{{ $menuProduct->product->name }}" 
                                                                     class="me-3" 
                                                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                                <div>
                                                                    <strong>{{ $menuProduct->product->name }}</strong>
                                                                    <br>
                                                                    <small class="text-muted">{{ __('admin.order') }}: {{ $menuProduct->order }}</small>
                                                                </div>
                                                            @else
                                                                <span class="text-muted">{{ __('admin.product_deleted') }}</span>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <span class="badge bg-info me-2">{{ __('admin.order') }}: <span class="order-value">{{ $menuProduct->order }}</span></span>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-info">
                                <i class="icon-base ti tabler-info-circle me-2"></i>
                                {{ __('admin.no_products_in_menu') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('dashboard-head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.css">
    <style>
        .sortable-products {
            min-height: 50px;
        }
        .product-item {
            cursor: move;
            transition: background-color 0.2s;
        }
        .product-item:hover {
            background-color: #f8f9fa;
        }
        .product-item.sortable-ghost {
            opacity: 0.4;
            background-color: #e9ecef;
        }
        .product-item.sortable-drag {
            opacity: 0.8;
        }
    </style>
@endsection

@section('dashboard-footer')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        'use strict';
        document.addEventListener('DOMContentLoaded', function() {
            const menuId = {{ $menu->id }};
            let hasChanges = false;
            const saveOrderBtn = document.getElementById('saveOrderBtn');

            // Initialize Sortable for each category
            const sortableLists = document.querySelectorAll('.sortable-products');
            sortableLists.forEach(function(list) {
                new Sortable(list, {
                    animation: 150,
                    handle: '.icon-base',
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onEnd: function(evt) {
                        hasChanges = true;
                        saveOrderBtn.style.display = 'block';
                        updateOrderNumbers(list);
                    }
                });
            });

            // Update order numbers in the UI
            function updateOrderNumbers(list) {
                const items = list.querySelectorAll('.product-item');
                items.forEach(function(item, index) {
                    const orderValue = item.querySelector('.order-value');
                    if (orderValue) {
                        orderValue.textContent = index;
                    }
                    item.setAttribute('data-order', index);
                });
            }

            // Save order
            saveOrderBtn.addEventListener('click', function() {
                const products = [];
                
                document.querySelectorAll('.sortable-products').forEach(function(list) {
                    const items = list.querySelectorAll('.product-item');
                    items.forEach(function(item, index) {
                        const menuProductId = item.getAttribute('data-menu-product-id');
                        if (menuProductId) {
                            products.push({
                                menu_product_id: menuProductId,
                                order: index
                            });
                        }
                    });
                });

                if (products.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: '{{ __("admin.warning") }}',
                        text: '{{ __("admin.no_products_to_save") }}'
                    });
                    return;
                }

                // Show loading
                saveOrderBtn.disabled = true;
                saveOrderBtn.innerHTML = '<i class="icon-base ti tabler-loader me-1"></i> {{ __("admin.saving") }}...';

                fetch('{{ route("menus.products.update-order", $menu->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ products: products })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("admin.success") }}',
                            text: data.message || '{{ __("admin.update_success") }}',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        hasChanges = false;
                        saveOrderBtn.style.display = 'none';
                        // Reload page after 1 second
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("admin.error") }}',
                            text: data.message || '{{ __("admin.update_error") }}'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("admin.error") }}',
                        text: '{{ __("admin.update_error") }}'
                    });
                })
                .finally(() => {
                    saveOrderBtn.disabled = false;
                    saveOrderBtn.innerHTML = '<i class="icon-base ti tabler-device-floppy"></i> {{ __("admin.save_order") }}';
                });
            });

            // Warn before leaving if there are unsaved changes
            window.addEventListener('beforeunload', function(e) {
                if (hasChanges) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        });
    </script>
@endsection
