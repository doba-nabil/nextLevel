@extends('dashboard.layout.master')
@section('title', __('admin.create') .' . '. __('admin.menu'))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ __('admin.create') .' '. __('admin.menu') }}</h5>
                    <div class="card-body">
                        <form id="formValidationExamples" class="row g-6" method="POST"
                              action="{{ route('menus.store') }}" enctype="multipart/form-data">
                            @csrf

                            <ul class="nav nav-tabs" id="langTabs" role="tablist">
                                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                                id="{{$localeCode}}-tab" data-bs-toggle="tab"
                                                data-bs-target="#{{$localeCode}}" type="button"
                                                role="tab">{{ __('admin.'.$properties['name']) }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content mt-3 p-3" id="langTabsContent">
                                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                         id="{{$localeCode}}" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label">{{ __('admin.name') }}</label>
                                                <input value="{{ old("name.$localeCode") }}" type="text" class="form-control" name="name[{{$localeCode}}]"
                                                       placeholder="{{ __('admin.name') }}">
                                                @error("name.$localeCode")
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">{{ __('admin.content') }}</label>
                                                <textarea class="form-control editor"
                                                          name="content[{{$localeCode}}]"
                                                          rows="5"
                                                          placeholder="{{ __('admin.content') }}">{{ old("content.$localeCode") }}</textarea>
                                                @error("content.$localeCode")
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.status') }}</label>
                                    <select id="is_active" class="select2 form-control" name="is_active"
                                            style="width:100%;">
                                        <option value="1">{{ __('admin.active') }}</option>
                                        <option value="0">{{ __('admin.deactive') }}</option>
                                    </select>
                                    @error('is_active')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <h5 class="mb-3"> {{ app()->getLocale() == 'ar'  ? 'اضافة منتج' : ' Add Product'}}</h5>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('admin.category') }}</label>
                                            <select id="selected_category_id" class="select2 form-control"
                                                    style="width:100%;">
                                                <option value="">{{ __('admin.select_category') }}</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->getTranslation('name', app()->getLocale()) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('admin.products') }}</label>
                                            <select id="selected_product_id" class="form-control product-search"
                                                    style="width:100%;">
                                                <option value="">{{ __('admin.select_product') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_price_check" checked>
                                            <label class="form-check-label" for="show_price_check">
                                                {{ app()->getLocale() == 'ar'  ? 'رؤية السعر' : ' Show Price'}}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" id="add_product_btn" class="btn btn-primary">
                                            {{ app()->getLocale() == 'ar'  ? 'اضافة منتج' : ' Add Product'}}
                                        </button>
                                        <button type="button" id="select_all_btn" class="btn btn-success ms-2">
                                            {{ app()->getLocale() == 'ar'  ? 'اختيار الكل' : ' Select All'}}
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="products_table">
                                        <thead>
                                        <tr>
                                            <th> {{ app()->getLocale() == 'ar'  ? ' منتج' : '  Product'}}</th>
                                            <th> {{ app()->getLocale() == 'ar'  ? ' تصنيف' : '  Category'}}</th>
                                            <th> {{ app()->getLocale() == 'ar'  ? 'عرض السعر' : ' Show Price'}}</th>
                                            <th> {{ app()->getLocale() == 'ar'  ? 'الترتيب' : ' Order'}}</th>
                                            <th>{{ __('admin.actions') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody id="products_table_body">
                                        </tbody>
                                    </table>
                                </div>
                                <input type="hidden" name="products_data" id="products_data" value="[]">
                                @error('products')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="dropzone needsclick" id="dropzone-basic">
                                    <div class="dz-message needsclick">
                                        {{__('admin.Drop files here or click to upload')}}
                                    </div>
                                </div>
                                @error("image")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('dashboard-head')
    @include('dashboard.partials.create.css')
@endsection

@section('dashboard-footer')
    @include('dashboard.partials.create.js')
    @php
        $messages = [
            'name_required' => __('admin.name_required'),
            'required' => __('admin.required'),
            'name_length' => __('admin.name_length'),
        ];
    @endphp
    <script>
        'use strict';

        document.addEventListener('DOMContentLoaded', function () {
            const menuForm = document.getElementById('formValidationExamples');

            if (menuForm) {
                const messages = @json($messages);

                const fv = FormValidation.formValidation(menuForm, {
                    fields: {
                        "name[ar]": {
                            validators: {
                                notEmpty: {message: messages.required},
                                stringLength: {
                                    min: 3,
                                    max: 50,
                                    message: messages.name_length
                                }
                            }
                        },
                        "name[en]": {
                            validators: {
                                notEmpty: {message: messages.required},
                                stringLength: {
                                    min: 3,
                                    max: 50,
                                    message: messages.name_length
                                }
                            }
                        },
                        "image": {
                            validators: {
                                notEmpty: {message: messages.required},
                                file: {
                                    extension: 'jpg,jpeg,png',
                                    type: 'image/jpeg,image/jpg,image/png',
                                }
                            }
                        }
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap5: new FormValidation.plugins.Bootstrap5(),
                        submitButton: new FormValidation.plugins.SubmitButton(),
                        autoFocus: new FormValidation.plugins.AutoFocus(),
                        defaultSubmit: new FormValidation.plugins.DefaultSubmit()
                    }
                });

                fv.on('core.form.invalid', function () {
                    const firstInvalidField = menuForm.querySelector('.is-invalid');

                    if (firstInvalidField) {
                        const tabPane = firstInvalidField.closest('.tab-pane');
                        if (tabPane) {
                            const tabId = tabPane.getAttribute('id');
                            const tabTrigger = document.querySelector(`[data-bs-target="#${tabId}"]`);

                            if (tabTrigger) {
                                const tabName = tabTrigger.innerText.trim();

                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __('admin.validation_error') }}',
                                    text: ` "${tabName}"{{ __('admin.validation_error_lang') }} `,
                                    confirmButtonText: '{{ __('admin.ok') }}'
                                });
                            }
                        }
                    }
                });
            }

            // Product selection logic
            let productsData = [];

            const categorySelect = document.getElementById('selected_category_id');
            const productSelect = document.getElementById('selected_product_id');
            const showPriceCheck = document.getElementById('show_price_check');
            const addProductBtn = document.getElementById('add_product_btn');
            const selectAllBtn = document.getElementById('select_all_btn');
            const productsTableBody = document.getElementById('products_table_body');
            const productsDataInput = document.getElementById('products_data');

            // Load all categories (global and menu-specific) initially
            function loadCategories() {
                categorySelect.innerHTML = '<option value="">{{ __('admin.select_category') }}</option>';
                productSelect.innerHTML = '<option value="">{{ __('admin.select_product') }}</option>';

                fetch(`{{ route('menus.get-categories') }}`)
                    .then(response => response.json())
                    .then(categories => {
                        const locale = '{{ app()->getLocale() }}';
                        categories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.id;
                            option.textContent = category.translated_name || (category.name && category.name[locale]) || category.name || '';
                            categorySelect.appendChild(option);
                        });
                    });
            }

            // Load global categories initially
            loadCategories();

            // Initialize Select2 for product search with AJAX
            // Wait for jQuery and Select2 to be loaded
            function initProductSearch() {
                if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                    // Destroy existing select2 if any
                    if ($('#selected_product_id').hasClass('select2-hidden-accessible')) {
                        $('#selected_product_id').select2('destroy');
                    }

                    $('#selected_product_id').select2({
                        placeholder: '{{ __('admin.select_product') }}',
                        allowClear: true,
                        minimumInputLength: 0,
                        dropdownParent: $('#selected_product_id').parent(),
                        language: {
                            noResults: function() {
                                return '{{ __('admin.no_results_found') }}';
                            },
                            searching: function() {
                                return '{{ __('admin.searching') }}...';
                            }
                        },
                        ajax: {
                            url: '{{ route('menus.products') }}',
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    search: params.term || '', // search term
                                    category_id: categorySelect.value || null
                                };
                            },
                            processResults: function (data) {
                                if (!data || !Array.isArray(data)) {
                                    console.error('Invalid response from server:', data);
                                    return { results: [] };
                                }
                                // Get dashboard locale from session or URL, not website locale
                                const locale = '{{ session('admin_locale', request()->segment(1) ?: config('app.locale')) }}';
                                // Get list of already added product IDs
                                const addedProductIds = productsData.map(p => String(p.id));
                                
                                return {
                                    results: data
                                        .filter(function(product) {
                                            // Filter out products that are already added
                                            return !addedProductIds.includes(String(product.id));
                                        })
                                        .map(function(product) {
                                            let name = '';
                                            if (typeof product.name === 'object' && product.name !== null) {
                                                // Prioritize current locale
                                                if (locale === 'en' || locale === 'en_US' || locale === 'en-GB') {
                                                    name = (product.name['en'] && String(product.name['en']).trim() !== '') 
                                                        ? String(product.name['en']).trim() 
                                                        : (product.name['ar'] && String(product.name['ar']).trim() !== '' ? String(product.name['ar']).trim() : '');
                                                } else {
                                                    name = (product.name['ar'] && String(product.name['ar']).trim() !== '') 
                                                        ? String(product.name['ar']).trim() 
                                                        : (product.name['en'] && String(product.name['en']).trim() !== '' ? String(product.name['en']).trim() : '');
                                                }
                                            } else {
                                                name = product.name || '';
                                            }
                                            const categoryName = product.category_name
                                                ? (product.category_name.translated || (typeof product.category_name === 'object'
                                                    ? (product.category_name[locale] || product.category_name[Object.keys(product.category_name)[0]] || product.category_name[Object.keys(product.category_name)[1]] || '')
                                                    : (product.category_name || '')))
                                                : '';
                                            return {
                                                id: product.id,
                                                text: name,
                                                category_id: product.category_id || null,
                                                category_name: categoryName
                                            };
                                        })
                                };
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                console.error('AJAX Error:', textStatus, errorThrown);
                                console.error('Response:', jqXHR.responseText);
                            },
                            cache: false
                        }
                    });
                    console.log('Product search Select2 initialized successfully');
                } else {
                    // Retry if jQuery/Select2 not loaded yet
                    setTimeout(initProductSearch, 100);
                }
            }

            // Initialize after page loads
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(initProductSearch, 500);
                });
            } else {
                setTimeout(initProductSearch, 500);
            }

            // Update category_id in AJAX request when category changes
            categorySelect.addEventListener('change', function() {
                // Clear product select and reload products for selected category
                if (typeof $ !== 'undefined') {
                    $('#selected_product_id').val(null).trigger('change');
                    // Trigger select2 to load products for the new category
                    $('#selected_product_id').select2('open');
                }
            });

            // Add product to list
            addProductBtn.addEventListener('click', function() {
                if (typeof $ === 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('admin.error') }}',
                        text: 'jQuery is not loaded'
                    });
                    return;
                }

                const productId = $('#selected_product_id').val();
                const showPrice = showPriceCheck.checked ? 1 : 0;

                if (!productId) {
                    Swal.fire({
                        icon: 'warning',
                        title: '{{ __('admin.warning') }}',
                        text: '{{ __('admin.please_select_product') }}'
                    });
                    return;
                }

                // Get selected product data from Select2
                const selectedOption = $('#selected_product_id').select2('data')[0];
                const productName = selectedOption ? selectedOption.text : '';
                const productCategoryId = selectedOption ? (selectedOption.category_id || null) : null;

                // Get category name - prefer from product data, then from dropdown, else uncategorized
                let categoryName = '';
                if (selectedOption && selectedOption.category_name) {
                    categoryName = selectedOption.category_name;
                } else {
                    const categoryOption = categorySelect.options[categorySelect.selectedIndex];
                    if (categoryOption && categoryOption.value) {
                        categoryName = categoryOption.textContent;
                    } else {
                        categoryName = '{{ __('admin.uncategorized') }}';
                    }
                }

                // Use product's category_id if available, otherwise use selected category
                const finalCategoryId = productCategoryId || categorySelect.value;

                // Check if product already added
                if (productsData.find(p => p.id == productId)) {
                    Swal.fire({
                        icon: 'info',
                        title: '{{ __('admin.info') }}',
                        text: '{{ __('admin.product_already_added') }}'
                    });
                    return;
                }

                const productData = {
                    id: productId,
                    category_id: finalCategoryId,
                    name: productName,
                    category: categoryName,
                    show_price: showPrice
                };

                productsData.push(productData);
                updateProductsTable();

                // Clear the product selection
                $('#selected_product_id').val(null).trigger('change');
            });

            // Select all products from the selected category
            selectAllBtn.addEventListener('click', function() {
                if (typeof $ === 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('admin.error') }}',
                        text: 'jQuery is not loaded'
                    });
                    return;
                }

                const categoryId = categorySelect.value;
                if (!categoryId) {
                    Swal.fire({
                        icon: 'warning',
                        title: '{{ __('admin.warning') }}',
                        text: '{{ __('admin.please_select_category') }}'
                    });
                    return;
                }

                const showPrice = showPriceCheck.checked ? 1 : 0;
                const locale = '{{ app()->getLocale() }}';
                const addedProductIds = productsData.map(p => String(p.id));

                // Get category name
                const categoryOption = categorySelect.options[categorySelect.selectedIndex];
                const categoryName = categoryOption ? categoryOption.textContent : '{{ __('admin.uncategorized') }}';

                // Fetch all products from the selected category
                fetch(`{{ route('menus.products') }}?category_id=${categoryId}&search=`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data || !Array.isArray(data)) {
                            console.error('Invalid response from server:', data);
                            return;
                        }

                        // Filter out already added products
                        const productsToAdd = data.filter(product => {
                            return !addedProductIds.includes(String(product.id));
                        });

                        if (productsToAdd.length === 0) {
                            Swal.fire({
                                icon: 'info',
                                title: '{{ __('admin.info') }}',
                                text: '{{ __('admin.all_products_already_added') ?? 'All products from this category are already added' }}'
                            });
                            return;
                        }

                        // Add all products
                        productsToAdd.forEach(product => {
                            const name = typeof product.name === 'object'
                                ? (product.name[locale] || product.name[Object.keys(product.name)[0]] || product.name[Object.keys(product.name)[1]] || '')
                                : (product.name || '');

                            const productCategoryName = product.category_name
                                ? (product.category_name.translated || (typeof product.category_name === 'object'
                                    ? (product.category_name[locale] || product.category_name[Object.keys(product.category_name)[0]] || product.category_name[Object.keys(product.category_name)[1]] || '')
                                    : (product.category_name || '')))
                                : categoryName;

                            const productData = {
                                id: String(product.id),
                                category_id: String(product.category_id || categoryId),
                                name: name,
                                category: productCategoryName,
                                show_price: showPrice
                            };

                            productsData.push(productData);
                        });

                        updateProductsTable();
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __('admin.success') }}',
                            text: `{{ __('admin.products_added') ?? 'Products added' }}: ${productsToAdd.length}`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching products:', error);
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __('admin.error') }}',
                            text: '{{ __('admin.error_loading_products') ?? 'Error loading products' }}'
                        });
                    });
            });

            function updateProductsTable() {
                productsTableBody.innerHTML = '';
                productsData.forEach((product, index) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${product.name}</td>
                        <td>${product.category}</td>
                        <td>${product.show_price ? '{{ __('admin.yes') }}' : '{{ __('admin.no') }}'}</td>
                        <td>${index + 1}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-product" data-index="${index}">
                                {{ __('admin.remove') }}
                            </button>
                        </td>
                    `;
                    productsTableBody.appendChild(row);
                });

                // Update hidden input
                productsDataInput.value = JSON.stringify(productsData.map(p => ({
                    id: parseInt(p.id),
                    category_id: parseInt(p.category_id),
                    show_price: p.show_price
                })));

                // Add remove event listeners
                document.querySelectorAll('.remove-product').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const index = parseInt(this.dataset.index);
                        productsData.splice(index, 1);
                        updateProductsTable();
                    });
                });
            }

            // Update form to send products_data instead of products[]
            const form = document.getElementById('formValidationExamples');
            form.addEventListener('submit', function(e) {
                if (productsData.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: '{{ __('admin.warning') }}',
                        text: '{{ __('admin.please_add_at_least_one_product') }}'
                    });
                    return false;
                }
            });
        });
    </script>
    @include('dashboard.partials.image-cropper-js')
    <script>
        'use strict';
        document.addEventListener('DOMContentLoaded', function () {
            initImageCropper('#dropzone-basic', 'image');
        });
    </script>
@endsection


