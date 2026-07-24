@extends('dashboard.layout.master')
@section('title', __('admin.order_meals'))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('meals.index') }}" class="btn btn-sm btn-secondary me-2">
                                <i class="icon-base ti tabler-arrow-left"></i> {{ __('admin.back') }}
                            </a>
                            <span>{{ __('admin.order_meals') }}</span>
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

                        <div class="alert alert-info mb-4">
                            <i class="icon-base ti tabler-info-circle me-2"></i>
                            {{ __('admin.drag_drop_instruction') }}
                        </div>

                        @if(count($categories) > 0 || count($mealsWithoutCategory) > 0)
                            <!-- Sortable Categories Container -->
                            <div id="sortable-categories" class="mb-4">
                                @foreach($categories as $category)
                                    <div class="category-section mb-4" data-category-id="{{ $category->id }}" data-order="{{ $category->order }}">
                                        <div class="card border">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center category-header" style="cursor: move;">
                                                <div>
                                                    <i class="icon-base ti tabler-grip-vertical me-2 text-muted"></i>
                                                    <strong>{{ $category->name }}</strong>
                                                    <span class="badge bg-primary ms-2">{{ count($category->products) }} {{ __('admin.meals') }}</span>
                                                    <span class="badge bg-info ms-2">{{ __('admin.order') }}: <span class="category-order-value">{{ $category->order }}</span></span>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-group sortable-meals" data-category-id="{{ $category->id }}">
                                                    @foreach($category->products as $meal)
                                                        <li class="list-group-item d-flex justify-content-between align-items-center meal-item" 
                                                            data-product-id="{{ $meal->id }}"
                                                            data-order="{{ $meal->order }}">
                                                            <div class="d-flex align-items-center">
                                                                <i class="icon-base ti tabler-grip-vertical me-2 text-muted" style="cursor: move;"></i>
                                                                @php
                                                                    $imageUrl = $meal->getFirstMediaUrl('products') ?: asset('website/assets/img/th.png');
                                                                @endphp
                                                                <img src="{{ $imageUrl }}" 
                                                                     alt="{{ $meal->name }}" 
                                                                     class="me-3" 
                                                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                                <div>
                                                                    <strong>{{ $meal->name }}</strong>
                                                                    <br>
                                                                    <small class="text-muted">{{ __('admin.order') }}: <span class="meal-order-value">{{ $meal->order }}</span></small>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <span class="badge bg-info">{{ __('admin.order') }}: <span class="meal-order-value">{{ $meal->order }}</span></span>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if(count($mealsWithoutCategory) > 0)
                                <div class="category-section mb-4" data-category-id="null">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="icon-base ti tabler-category me-2"></i>
                                                {{ __('admin.without_category') }}
                                                <span class="badge bg-secondary ms-2">{{ count($mealsWithoutCategory) }} {{ __('admin.meals') }}</span>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group sortable-meals" data-category-id="null">
                                                @foreach($mealsWithoutCategory as $meal)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center meal-item" 
                                                        data-product-id="{{ $meal->id }}"
                                                        data-order="{{ $meal->order }}">
                                                        <div class="d-flex align-items-center">
                                                            <i class="icon-base ti tabler-grip-vertical me-2 text-muted" style="cursor: move;"></i>
                                                            @php
                                                                $imageUrl = $meal->getFirstMediaUrl('products') ?: asset('website/assets/img/th.png');
                                                            @endphp
                                                            <img src="{{ $imageUrl }}" 
                                                                 alt="{{ $meal->name }}" 
                                                                 class="me-3" 
                                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                            <div>
                                                                <strong>{{ $meal->name }}</strong>
                                                                <br>
                                                                <small class="text-muted">{{ __('admin.order') }}: <span class="meal-order-value">{{ $meal->order }}</span></small>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <span class="badge bg-info">{{ __('admin.order') }}: <span class="meal-order-value">{{ $meal->order }}</span></span>
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
                                {{ __('admin.no_meals_found') }}
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
        .sortable-meals {
            min-height: 50px;
        }
        .meal-item, .category-section {
            cursor: move;
            transition: background-color 0.2s;
        }
        .meal-item:hover {
            background-color: #f8f9fa;
        }
        .meal-item.sortable-ghost {
            opacity: 0.4;
            background-color: #e9ecef;
        }
        .meal-item.sortable-drag {
            opacity: 0.8;
        }
        .category-section.sortable-ghost {
            opacity: 0.4;
            background-color: #e9ecef;
        }
        .category-section.sortable-drag {
            opacity: 0.8;
        }
        .category-header:hover {
            background-color: #f0f0f0 !important;
        }
    </style>
@endsection

@section('dashboard-footer')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        'use strict';
        document.addEventListener('DOMContentLoaded', function() {
            let hasChanges = false;
            const saveOrderBtn = document.getElementById('saveOrderBtn');

            // Initialize Sortable for categories
            const categoriesContainer = document.getElementById('sortable-categories');
            if (categoriesContainer) {
                new Sortable(categoriesContainer, {
                    animation: 150,
                    handle: '.category-header',
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onEnd: function(evt) {
                        hasChanges = true;
                        saveOrderBtn.style.display = 'block';
                        updateCategoryOrderNumbers();
                    }
                });
            }

            // Initialize Sortable for meals within each category
            const mealLists = document.querySelectorAll('.sortable-meals');
            mealLists.forEach(function(list) {
                new Sortable(list, {
                    animation: 150,
                    handle: '.icon-base',
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    group: 'meals', // Allow dragging between categories
                    onEnd: function(evt) {
                        hasChanges = true;
                        saveOrderBtn.style.display = 'block';
                        updateMealOrderNumbers(list);
                        // Update category_id if moved to different category
                        const newCategoryId = list.getAttribute('data-category-id');
                        const movedItem = evt.item;
                        movedItem.setAttribute('data-category-id', newCategoryId);
                    }
                });
            });

            // Update category order numbers in the UI
            function updateCategoryOrderNumbers() {
                const categories = document.querySelectorAll('.category-section[data-category-id]');
                categories.forEach(function(category, index) {
                    const orderValue = category.querySelector('.category-order-value');
                    if (orderValue) {
                        orderValue.textContent = index;
                    }
                    category.setAttribute('data-order', index);
                });
            }

            // Update meal order numbers in the UI
            function updateMealOrderNumbers(list) {
                const items = list.querySelectorAll('.meal-item');
                items.forEach(function(item, index) {
                    const orderValues = item.querySelectorAll('.meal-order-value');
                    orderValues.forEach(function(orderValue) {
                        orderValue.textContent = index;
                    });
                    item.setAttribute('data-order', index);
                });
            }

            // Save order
            saveOrderBtn.addEventListener('click', function() {
                // Collect categories order
                const categories = [];
                document.querySelectorAll('.category-section[data-category-id]:not([data-category-id="null"])').forEach(function(category, index) {
                    const categoryId = category.getAttribute('data-category-id');
                    if (categoryId && categoryId !== 'null') {
                        categories.push({
                            category_id: categoryId,
                            order: index
                        });
                    }
                });

                // Collect meals order
                const meals = [];
                document.querySelectorAll('.sortable-meals').forEach(function(list) {
                    const categoryId = list.getAttribute('data-category-id');
                    const items = list.querySelectorAll('.meal-item');
                    items.forEach(function(item, index) {
                        const productId = item.getAttribute('data-product-id');
                        if (productId) {
                            meals.push({
                                product_id: productId,
                                category_id: categoryId === 'null' ? null : categoryId,
                                order: index
                            });
                        }
                    });
                });

                if (categories.length === 0 && meals.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: '{{ __("admin.warning") }}',
                        text: '{{ __("admin.no_items_to_save") }}'
                    });
                    return;
                }

                // Show loading
                saveOrderBtn.disabled = true;
                saveOrderBtn.innerHTML = '<i class="icon-base ti tabler-loader me-1"></i> {{ __("admin.saving") }}...';

                // Save categories order
                const saveCategories = categories.length > 0 ? fetch('{{ route("meals.categories.update-order") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ categories: categories })
                }).then(response => response.json()) : Promise.resolve({ status: 'success' });

                // Save meals order
                const saveMeals = meals.length > 0 ? fetch('{{ route("meals.products.update-order") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ meals: meals })
                }).then(response => response.json()) : Promise.resolve({ status: 'success' });

                // Wait for both to complete
                Promise.all([saveCategories, saveMeals])
                    .then(([categoriesResult, mealsResult]) => {
                        if (categoriesResult.status === 'success' && mealsResult.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: '{{ __("admin.success") }}',
                                text: categoriesResult.message || mealsResult.message || '{{ __("admin.update_success") }}',
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
                                text: categoriesResult.message || mealsResult.message || '{{ __("admin.update_error") }}'
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
