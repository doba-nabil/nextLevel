@extends('website.layout.master')
@section('title', __('website.categories'))
@section('body', false)
@section('website-main')
    <!-- CSS Link -->
    <link rel="stylesheet" href="{{ asset('website/assets/css/menu_redesign.css') }}">

    <!-- Content -->
    <div class="breadCrumb_section midPadding">
        <div class="container px-lg-0">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('website.home') }}">{{ __('website.home') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('website.categories') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="pickup_section secPadding pt-0">
        <div class="container {{ count($categories) == 0 ? '' : ' px-lg-0 ' }}">
            <div class="row">
                @if(count($categories) == 0)
                <div class="col-12 col-lg-12 my-5 py-5 text-center">
                    <h4 class="alert alert-warning border-0 bg-light text-muted">
                        {{ __('website.no_products_category') }}
                    </h4>
                </div>
                @else

                <!-- Premium Delicate Category Selector -->
                <div class="col-12">
                    <div class="menu_selector_wrapper">
                        <div class="menu_dropdown_label">{{ __('website.select_category') ?? 'Discover Categories' }}</div>

                        <div class="custom_dropdown" id="categoryDropdown">
                            <div class="dropdown_trigger" onclick="toggleCategoryDropdown()">
                                <div class="selected_val">
                                    @php
                                        $settingModel = \App\Models\Setting::getSettingModel();
                                        $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
                                        $activeCatImage = null;
                                        if ($currentCategory) {
                                            if (isset($currentCategory->is_special) && $currentCategory->is_special) {
                                                $activeCatImage = $currentCategory->image_url ?? null;
                                            } else {
                                                $activeCatImage = $currentCategory->getFirstMediaUrl('categories');
                                            }
                                        }
                                    @endphp
                                    <img src="{{ $activeCatImage ?: $logoUrl }}" class="cat_thumb_mini" alt="">
                                    <span>{{ $currentCategory ? $currentCategory->name : __('website.select_category') }}</span>
                                </div>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="dropdown_options">
                                @foreach($categories as $category)
                                    @php
                                        $isActive = isset($currentCategory) && $currentCategory->id == $category->id;
                                        if (isset($category->is_special) && $category->is_special) {
                                            $categoryImage = $category->image_url ?? null;
                                        } else {
                                            $categoryImage = $category->getFirstMediaUrl('categories');
                                        }
                                    @endphp
                                    <a href="{{ route('website.categories', $category->slug.'?type='.$type) }}"
                                       class="category_option {{ $isActive ? 'active_option' : '' }}">
                                        <img src="{{ $categoryImage ?: $logoUrl }}" class="cat_thumb_mini" alt="">
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="row incat_row">
                        @if(isset($products) && $products->count())
                            @foreach($products as $product)
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="trend_cardN1">
                                        @auth('web')
                                            <button
                                                class="absAdd_fav d__mob__none{{ $favoriteProductIds->contains($product->id) ? ' favorited' : '' }}"
                                                data-product-id="{{ $product->id }}"
                                                style="{{ $favoriteProductIds->contains($product->id) ? 'background-color: #ff4444;' : '' }}">
                                                <i class="las la-heart"></i>
                                            </button>
                                        @endauth
                                        <a href="{{ route('website.products', $product->slug) }}"
                                            class="trendThumb_link">
                                            @php
                                                $productImage = $product->getFirstMediaUrl('products');
                                                $hasImage = !empty($productImage);
                                            @endphp
                                            <img src="{{ $productImage ?: $logoUrl }}" alt="{{ $product->name }}"
                                                class="trendThumb_img {{ !$hasImage ? 'no-product-image' : '' }}"
                                                loading="lazy" decoding="async">
                                        </a>
                                        <div class="trendCont_box">
                                            <h5 class="pro_title"><a
                                                    href="{{ route('website.products', $product->slug) }}">{{ $product->name }}</a>
                                            </h5>
                                            <div class="content_bInfo">
                                                <span class="health_status">{{ __('website.healthy') }}</span>
                                                <div class="pro_price">
                                                    @php
                                                        $priceDetails = $product->getPriceDetails(session('currency'));
                                                    @endphp
                                                    @if ($priceDetails['has_discount'])
                                                        <span class="price-before"
                                                            style="text-decoration: line-through; color: #999; margin-inline-end: 8px;">
                                                            {{ number_format($priceDetails['original'], 2) }}
                                                            {{ \App\Models\Currency::getCurrentCurrencySign() }}
                                                        </span>
                                                        <span class="price-after"
                                                            style="color: #f6d814; font-weight: bold;">
                                                            {{ number_format($priceDetails['discounted'], 2) }}
                                                            {{ \App\Models\Currency::getCurrentCurrencySign() }}
                                                        </span>
                                                    @else
                                                        {{ number_format($priceDetails['discounted'], 2) }}
                                                        {{ \App\Models\Currency::getCurrentCurrencySign() }}
                                                    @endif
                                                </div>
                                                @if ($product->calories)
                                                    <div class="kcal_flex">
                                                        <i class="kcal_icon"></i>
                                                        <span>{{ $product->calories }}kcal</span>
                                                    </div>
                                                @endif
                                                <div class="fats_info">
                                                    @foreach ($product->definitions as $definition)
                                                        <div class="carbIN_flex">
                                                            <span> {{ $definition->name }} </span>
                                                            <strong>{{ rtrim(rtrim(number_format((float) $definition->pivot->value, 2, '.', ''), '0'), '.') }}{{ $definition->unit }}</strong>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @if (isset($cartProductIds[$product->id]))
                                                @php
                                                    $hasAddons =
                                                        $product->addonGroups->count() > 0 ||
                                                        $product->addons->count() > 0;
                                                @endphp
                                                <div class="number__spinner wide_spinner w-100 product-spinner-{{ $product->id }}"
                                                    data-product-id="{{ $product->id }}"
                                                    data-product-slug="{{ $product->slug }}"
                                                    data-has-addons="{{ $hasAddons ? '1' : '0' }}">
                                                    <span class="ns-btn">
                                                        <a data-dir="up" tabindex="0">
                                                            <i class="fa fa-plus"></i>
                                                        </a>
                                                    </span>
                                                    <input type="text" class="pl-ns-value"
                                                        value="{{ $cartQuantities[$product->id] ?? 1 }}" maxlength="2"
                                                        readonly tabindex="0">
                                                    <span class="ns-btn">
                                                        <a data-dir="dwn" class="remove-product-btn" tabindex="0">
                                                            <i class="icon_trash"></i>
                                                        </a>
                                                    </span>
                                                </div>
                                            @else
                                                <div class="buttons_wrapper w-100 product-buttons-{{ $product->id }}">
                                                    @php
                                                        $hasAddons =
                                                            $product->addonGroups->count() > 0 ||
                                                            $product->addons->count() > 0;
                                                    @endphp
                                                    @if ($hasAddons)
                                                        <a href="{{ route('website.products', $product->slug) }}"
                                                            class="main_bttn hvr-sweep-to-right">{{ __('website.add_to_cart') }}</a>
                                                    @else
                                                        <a href="javascript:void(0)"
                                                            class="main_bttn hvr-sweep-to-right quick-add-to-cart"
                                                            data-product-id="{{ $product->id }}"
                                                            data-has-addons="0">{{ __('website.add_to_cart') }}</a>
                                                    @endif
                                                    <a href="{{ route('website.products', $product->slug) }}"
                                                        class="main_bttn white_bttn hvr-sweep-to-right">
                                                        {{ __('website.buy_now') }} </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <h4 class="alert alert-warning rounded-0 border-0">{{ __('website.no_products_category') }}</h4>
                        @endif
                    </div>
                </div>
                 @endif
            </div>
        </div>
    </section>
    <!--/ Content -->
@endsection

@section('website-footer')
    <script>
        function toggleCategoryDropdown() {
            var categoryDropdown = document.getElementById('categoryDropdown');
            if (categoryDropdown) categoryDropdown.classList.toggle('active');
        }

        window.onclick = function(event) {
            if (!event.target.matches('.dropdown_trigger') && !event.target.matches('.dropdown_trigger *')) {
                var dropdowns = document.getElementsByClassName("custom_dropdown");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('active')) {
                        openDropdown.classList.remove('active');
                    }
                }
            }
        }
    </script>
    <script>
        // Handle quick add to cart (products without addons)
        $(document).on('click', '.quick-add-to-cart', function(e) {
            e.preventDefault();

            // Validate city selection
            if (typeof validateCitySelection === 'function' && !validateCitySelection()) {
                return;
            }

            const $button = $(this);
            const productId = $button.data('product-id');
            let $buttonsWrapper = $button.closest('.product-buttons-' + productId);

            if ($buttonsWrapper.length === 0) {
                $buttonsWrapper = $button.closest('.buttons_wrapper');
            }

            const originalButtonHtml = $button.html();
            $button.addClass('disabled').text('{{ __("website.adding") }}...');

            $.ajax({
                url: "{{ route('cart.add') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    product_id: productId,
                    quantity: 1,
                    addons: [],
                    is_box: 0
                },
                success: function(response) {
                    if (response.status !== false) {
                        const cartCount = response.count || 0;
                        if ($('#cartCount').length) {
                            $('#cartCount').text(cartCount);
                            if (cartCount > 0) {
                                $('#cartCount').show();
                            }
                        }
                        if ($('#cartCountMobile').length) {
                            $('#cartCountMobile').text(cartCount);
                            if (cartCount > 0) {
                                $('#cartCountMobile').show();
                            }
                        }

                        const $productCard = $button.closest('.product_cardN1');
                        const $productLink = $productCard.find('a[href*="/products/"]');
                        const productSlug = $productLink.attr('href') ? $productLink.attr('href').split('/products/')[1] : '';
                        const hasAddons = $button.data('has-addons') || '0';

                        const spinnerHtml = `
                            <div class="number__spinner wide_spinner w-100 product-spinner-${productId}"
                                 data-product-id="${productId}"
                                 data-product-slug="${productSlug}"
                                 data-has-addons="${hasAddons}">
                                <span class="ns-btn">
                                    <a data-dir="up" tabindex="0">
                                        <i class="fa fa-plus"></i>
                                    </a>
                                </span>
                                <input type="text" class="pl-ns-value" value="1" maxlength="2" readonly tabindex="0">
                                <span class="ns-btn">
                                    <a data-dir="dwn" class="remove-product-btn" tabindex="0">
                                        <i class="icon_trash"></i>
                                    </a>
                                </span>
                            </div>
                        `;

                        if ($buttonsWrapper.length > 0) {
                            $buttonsWrapper.replaceWith(spinnerHtml);
                        } else {
                            $button.replaceWith(spinnerHtml);
                        }

                        if (response.cart_total && $('.total_box').length) {
                            $('.total_box').html('<strong>Total:</strong> ' + response.cart_total + ' ' + (response.currency || '{{ \App\Models\Currency::getCurrentCurrencySign() }}'));
                        }

                        if ($('#sideCart_menu .cartSide_content').length) {
                            $('#sideCart_menu .cartSide_content').load(window.location.href + " #sideCart_menu .cartSide_content > *");
                        }

                        AppSwal.success('{{ __("website.added_to_cart") }}');
                    } else {
                        AppSwal.error(response.message || '{{ __("website.something_went_wrong") }}');
                        $button.removeClass('disabled').html(originalButtonHtml);
                    }
                },
                error: function(xhr) {
                    console.error('Quick Add to Cart Error:', xhr);
                    let errorMessage = '{{ __("website.something_went_wrong") }}';

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }

                    AppSwal.error(errorMessage);
                    $button.removeClass('disabled').html(originalButtonHtml);
                }
            });
        });

        // Handle number spinner for cart quantity update
        function handleSpinnerClick(e) {
            e.preventDefault();

            const $button = $(this);
            const $spinner = $button.closest('.number__spinner');

            // Prevent multiple simultaneous clicks
            if ($spinner.hasClass('updating')) {
                return;
            }

            const $input = $spinner.find('.pl-ns-value');
            const productId = parseInt($spinner.data('product-id')) || 0;
            const direction = $button.data('dir');
            let currentQty = parseInt($input.val()) || 1;
            let newQty = currentQty;

            if (!productId) {
                AppSwal.error('{{ __("website.invalid_product") }}', '{{ __("website.error") }}');
                return;
            }

            if (direction === 'up') {
                newQty = currentQty + 1;
            } else if (direction === 'dwn') {
                newQty = Math.max(0, currentQty - 1);
            }

            if (newQty === 0) {
                $spinner.find('.ns-btn a').addClass('disabled');
                $input.addClass('loading');

                $.ajax({
                    url: "{{ route('cart.remove') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        product_id: productId
                    },
                    success: function(response) {
                        if (response.status) {
                            const productSlug = $spinner.data('product-slug') || '';
                            const hasAddons = $spinner.data('has-addons') === '1' || $spinner.data('has-addons') === 1;

                            let buttonsHtml = '<div class="buttons_wrapper w-100 product-buttons-' + productId + '">';

                            if (hasAddons) {
                                buttonsHtml += '<a href="{{ url("/products") }}/' + productSlug + '" class="main_bttn hvr-sweep-to-right">{{ __("website.add_to_cart") }}</a>';
                            } else {
                                buttonsHtml += '<a href="javascript:void(0)" class="main_bttn hvr-sweep-to-right quick-add-to-cart" data-product-id="' + productId + '" data-has-addons="0">{{ __("website.add_to_cart") }}</a>';
                            }
                            buttonsHtml += '<a href="{{ url("/products") }}/' + productSlug + '" class="main_bttn white_bttn hvr-sweep-to-right"> {{ __("website.buy_now") }} </a>';
                            buttonsHtml += '</div>';

                            $spinner.replaceWith(buttonsHtml);

                            const cartCount = response.count || 0;
                            if ($('#cartCount').length) {
                                $('#cartCount').text(cartCount);
                                if (cartCount > 0) {
                                    $('#cartCount').show();
                                } else {
                                    $('#cartCount').hide();
                                }
                            }
                            if ($('#cartCountMobile').length) {
                                $('#cartCountMobile').text(cartCount);
                                if (cartCount > 0) {
                                    $('#cartCountMobile').show();
                                } else {
                                    $('#cartCountMobile').hide();
                                }
                            }

                            if (response.total && $('.total_box').length) {
                                $('.total_box').html('<strong>Total:</strong> ' + response.total + ' ' + (response.currency || '{{ \App\Models\Currency::getCurrentCurrencySign() }}'));
                            }

                            if ($('#sideCart_menu .cartSide_content').length) {
                                $('#sideCart_menu .cartSide_content').load(window.location.href + " #sideCart_menu .cartSide_content > *");
                            }

                            AppSwal.success('{{ __("website.removed_from_cart") }}');
                        } else {
                            AppSwal.error(response.message || '{{ __("website.something_went_wrong") }}');
                            $spinner.find('.ns-btn a').removeClass('disabled');
                            $input.removeClass('loading');
                        }
                    },
                    error: function(xhr) {
                        AppSwal.error('{{ __("website.something_went_wrong") }}');
                        $spinner.find('.ns-btn a').removeClass('disabled');
                        $input.removeClass('loading');
                    }
                });
                return;
            }

            // Mark as updating to prevent duplicate calls
            $spinner.addClass('updating');
            $spinner.find('.ns-btn a').addClass('disabled');
            $input.addClass('loading');

            $.ajax({
                url: "{{ route('cart.update.quantity') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    product_id: productId,
                    quantity: newQty
                },
                success: function(response) {
                    if (response.status) {
                        $input.val(newQty);

                        const cartCount = response.count || 0;
                        if ($('#cartCount').length) {
                            $('#cartCount').text(cartCount);
                            if (cartCount > 0) {
                                $('#cartCount').show();
                            } else {
                                $('#cartCount').hide();
                            }
                        }
                        if ($('#cartCountMobile').length) {
                            $('#cartCountMobile').text(cartCount);
                            if (cartCount > 0) {
                                $('#cartCountMobile').show();
                            } else {
                                $('#cartCountMobile').hide();
                            }
                        }

                        if (response.total && $('.total_box').length) {
                            $('.total_box').html('<strong>Total:</strong> ' + response.total + ' ' + (response.currency || '{{ \App\Models\Currency::getCurrentCurrencySign() }}'));
                        }

                        if ($('#sideCart_menu .cartSide_content').length) {
                            $('#sideCart_menu .cartSide_content').load(window.location.href + " #sideCart_menu .cartSide_content > *");
                        }

                        AppSwal.success('{{ __("website.cart_updated") }}');
                    } else {
                        AppSwal.error(response.message);
                    }

                    $spinner.find('.ns-btn a').removeClass('disabled');
                    $input.removeClass('loading');
                    $spinner.removeClass('updating');
                },
                error: function(xhr) {
                    let errorMessage = '{{ __("website.something_went_wrong") }}';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    AppSwal.error(errorMessage);

                    $spinner.find('.ns-btn a').removeClass('disabled');
                    $input.removeClass('loading');
                    $spinner.removeClass('updating');
                }
            });
        }

        // Initialize spinner events
        $(document).on('click', '.number__spinner .ns-btn a', handleSpinnerClick);

        // Handle favorite button click (toggle favorite) - using event delegation
        $(document).on('click', '.absAdd_fav', function (e) {
            e.preventDefault();
            e.stopPropagation();

            // Check if user is authenticated
            @auth('web')
            var button = $(this);
            var productId = button.attr('data-product-id') || button.data('product-id');
            var isFavorited = button.hasClass('favorited');

            if (!productId) {
                console.error('Product ID is undefined or null');
                AppSwal.error('Product ID is missing. Value: ' + productId, '{{ __("website.an_error_occurred") }}');
                return;
            }

            $.ajax({
                url: isFavorited ? '{{ url(app()->getLocale() . "/profile/remove-favorite") }}' : '{{ url(app()->getLocale() . "/profile/add-favorite") }}',
                method: 'POST',
                data: {
                    product_id: productId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.success) {
                        button.toggleClass('favorited');

                        // Update background instead of image
                        if (button.hasClass('favorited')) {
                            button.css('background-color', '#ff4444'); // Red background when favorited
                            AppSwal.success('{{ __("website.product_added_to_favorites") }}', '{{ __("website.added_to_favorites") }}');
                        } else {
                            button.css('background-color', '#fff'); // White background when not favorited
                            AppSwal.success('{{ __("website.product_removed_from_favorites") }}', '{{ __("website.removed_from_favorites") }}');
                        }
                    }
                },
                error: function (xhr) {
                    AppSwal.error('{{ __("website.error_processing_favorite") }}', '{{ __("website.an_error_occurred") }}');
                }
            });
            @else
            // User not authenticated - redirect to login
            AppSwal.confirm({
                icon: 'warning',
                title: '{{ __("website.login_required") }}',
                text: '{{ __("website.please_login_to_add_favorites") }}',
                confirmButtonText: '{{ __("website.login") }}',
                cancelButtonText: '{{ __("website.cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '{{ route("website.login") }}';
                }
            });
            @endauth
        });
    </script>
@endsection
