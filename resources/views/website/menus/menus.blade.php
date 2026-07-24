@extends('website.layout.master')
@section('title', __('website.menus'))
@section('body', 'bg-white')
@section('website-main')
    <!-- CSS Link -->
    <link rel="stylesheet" href="{{ asset('website/assets/css/menu_redesign.css') }}">

    <!-- Content -->
    <div class="breadCrumb_section midPadding">
        <div class="container px-lg-0">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('website.home') }}">{{ __('website.home') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> {{ __('website.menus') }} </li>
                </ol>
            </nav>
        </div>
    </div>

    <!--start menus section-->
    <section class="pickup_section secPadding pt-0">
        <div class="container px-lg-0">
            @if($allMenus && $allMenus->count() > 0)

                <!-- 1. Menus as TABS (Center List) - KEEP AS IS -->
                <ul class="center_ListN d-none d-lg-flex" id="menuTabsList" style="margin-bottom: 30px;">
                    @foreach($allMenus as $index => $menu)
                        <li data-menu-slug="{{ $menu->slug }}">
                            <a href="{{ route('website.menus', ['menu_slug' => $menu->slug]) }}"
                               class="center_linkN {{ $active_menu && $active_menu->slug == $menu->slug ? 'active' : '' }}">
                                {{ $menu->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <!-- Mobile Menu Dropdown -->
                <div class="dropdown Pickup_dropdown d-lg-none mt-3 mb-4">
                    <a class="Pickup_bttn dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                       aria-expanded="false">
                        {{ $active_menu ? $active_menu->name : '' }}
                    </a>
                    <ul class="dropdown-menu">
                        @foreach($allMenus as $menu)
                            <li><a class="dropdown-item"
                                   href="{{ route('website.menus', ['menu_slug' => $menu->slug]) }}"> {{ $menu->name }} </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if($categories && $categories->count() > 0 && $active_category)

                    <!-- 2. Categories as DROPDOWN (Centered) - REPLACES SIDEBAR -->
                    <div class="menu_selector_wrapper">
                        <div class="menu_dropdown_label">{{ __('website.select_category') ?? 'Discover Categories' }}</div>

                        <div class="custom_dropdown" id="categoryDropdown">
                            <div class="dropdown_trigger" onclick="toggleCategoryDropdown()">
                                <div class="selected_val">
                                    @php
                                        $settingModel = \App\Models\Setting::getSettingModel();
                                        $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
                                        // Fix: active_category is an array, access the model via ['category']
                                        $activeCatModel = $active_category['category'] ?? null;
                                        $activeCatImage = $activeCatModel ? $activeCatModel->getFirstMediaUrl('categories') : null;
                                    @endphp
                                    <img src="{{ $activeCatImage ?: $logoUrl }}" class="cat_thumb_mini" alt="">
                                    <span>{{ $activeCatModel ? $activeCatModel->name : ($active_category['name'] ?? '') }}</span>
                                </div>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="dropdown_options">
                                @foreach($categories as $categoryData)
                                    @php
                                        $category = $categoryData['category'];
                                        $isActive = $active_category && $active_category['slug'] == $category->slug;
                                        $categoryImage = $category->getFirstMediaUrl('categories');
                                        $settingModel = \App\Models\Setting::getSettingModel();
                                        $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
                                    @endphp
                                    <a href="{{ route('website.menus', ['menu_slug' => $active_menu->slug, 'category_slug' => $category->slug]) }}"
                                       class="category_option {{ $isActive ? 'active_option' : '' }}">
                                        <img src="{{ $categoryImage ?: $logoUrl }}" class="cat_thumb_mini" alt="">
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- 3. Full Width Grid with 4 Columns -->
                    <div class="container">
                        <div class="row">

                            @if($active_category && isset($active_category['products']) && $active_category['products']->count() > 0)
                                <div class="row incat_row d-flex justify-content-center">
                                    @foreach($active_category['products'] as $item)
                                        @php
                                            $product = $item['product'];
                                            $showPrice = $item['show_price'];
                                        @endphp
                                        @if($product && $product->active)
                                            <!-- CHANGED: col-lg-3 for 4 items per row -->
                                            <div class="col-6 col-md-4 col-lg-3">
                                                <div class="product_cardN1">
                                                    @auth('web')
                                                        <button
                                                            class="absAdd_fav d__mob__none{{ $favoriteProductIds->contains($product->id) ? ' favorited' : '' }}"
                                                            data-product-id="{{ $product->id }}"
                                                            style="{{ $favoriteProductIds->contains($product->id) ? 'background-color: #ff4444;' : '' }}">
                                                            <i class="las la-heart"></i>
                                                        </button>
                                                    @endauth
                                                    <a href="{{ route('website.products', $product->slug) }}?from_menu=1" class="prodThumb_link">
                                                        @php
                                                            $productImage = $product->getFirstMediaUrl('products', 'thumb');
                                                            $hasImage = !empty($productImage);
                                                        @endphp
                                                        <img src="{{ $productImage ?: $logoUrl }}"
                                                             alt="{{ $product->name }}"
                                                             class="prodThumb_img {{ !$hasImage ? 'no-product-image' : '' }}" loading="lazy" decoding="async">
                                                    </a>
                                                    <div class="content_box">
                                                        <h5 class="pro_title">
                                                            <a href="{{ route('website.products', $product->slug) }}?from_menu=1"> {{ $product->name }} </a>
                                                        </h5>
                                                        <div class="content_bInfo">
                                                            <span
                                                                class="health_status">{{ __('website.healthy') }}</span>
                                                            @if($showPrice)
                                                                <div class="pro_price">
                                                                    {{ $product->getCurrentPrice(session('currency')) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}
                                                                </div>
                                                            @endif
                                                            @if($product->calories)
                                                                <div class="kcal_flex">
                                                                    <i class="kcal_icon"></i>
                                                                    <span>{{ $product->calories }}kcal</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    <div class="buttons_wrapper w-100">
                                                        <a href="{{ route('website.products', $product->slug) }}?from_menu=1"
                                                           class="main_bttn hvr-sweep-to-right w-100">{{ __('website.view') }}</a>
                                                    </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                            <div class="col-12">
                                <div class="alert alert-info border-0 text-center py-5" style="background: #fdfdfd;">
                                    <p style="font-size: 18px; color: #777;">{{ __('website.no_products_in_category') }}</p>
                                </div>
                            </div>
                            @endif

                    </div>
                    </div>
                @else
                    <div class="alert alert-info border-0 text-center py-5">
                        <p>{{ __('website.no_categories_in_menu') }}</p>
                    </div>
                @endif
            @else
                <div class="alert border-0 text-center py-5" style="background-color: #000; color: #fff;">
                    <h4 style="color: #fff;">{{ __('website.no_menus_available') }}</h4>
                </div>
            @endif
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

        document.addEventListener('DOMContentLoaded', function () {
            function centerActiveMenu() {
                const menuList = document.getElementById('menuTabsList');
                if (!menuList) return;

                const activeMenu = menuList.querySelector('.center_linkN.active');
                if (!activeMenu) return;

                const activeMenuItem = activeMenu.closest('li');
                if (!activeMenuItem) return;

                const container = menuList.parentElement;
                if (!container) return;

                const containerWidth = container.offsetWidth;
                const activeItemLeft = activeMenuItem.offsetLeft;
                const activeItemWidth = activeMenuItem.offsetWidth;
                const activeItemCenter = activeItemLeft + (activeItemWidth / 2);
                const scrollPosition = activeItemCenter - (containerWidth / 2);

                // Calculate max scroll (list width - container width)
                const maxScroll = menuList.scrollWidth - containerWidth;
                const finalScrollPosition = Math.max(0, Math.min(scrollPosition, maxScroll));

                container.scrollTo({
                    left: finalScrollPosition,
                    behavior: 'smooth'
                });
            }

            // Center on page load
            centerActiveMenu();

            // Also center after a short delay to ensure layout is complete
            setTimeout(centerActiveMenu, 100);

            // Center on window resize
            let resizeTimer;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(centerActiveMenu, 250);
            });
        });


        // Favorite Toggle
        $(document).on('click', '.absAdd_fav', function(e) {
            e.preventDefault();
            e.stopPropagation();

            @auth('web')
                var button = $(this);
                var productId = button.attr('data-product-id') || button.data('product-id');
                var isFavorited = button.hasClass('favorited');

                if (!productId) {
                    return;
                }

                $.ajax({
                    url: isFavorited ?
                        '{{ url(app()->getLocale() . '/profile/remove-favorite') }}' :
                        '{{ url(app()->getLocale() . '/profile/add-favorite') }}',
                    method: 'POST',
                    data: {
                        product_id: productId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            button.toggleClass('favorited');

                            if (button.hasClass('favorited')) {
                                button.css('background-color', '#ff4444');
                                AppSwal.success(
                                    '{{ __("website.product_added_to_favorites") }}',
                                    '{{ __("website.added_to_favorites") }}');
                            } else {
                                button.css('background-color', '#fff');
                                AppSwal.success(
                                    '{{ __("website.product_removed_from_favorites") }}',
                                    '{{ __("website.removed_from_favorites") }}');
                            }
                        }
                    }
                });
            @else
                window.location.href = '{{ route('website.login') }}';
            @endauth
        });
    </script>
@endsection
