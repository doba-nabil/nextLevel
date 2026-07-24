@extends('website.layout.master')
@section('title', $menu->name)
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
                    <li class="breadcrumb-item"><a href="{{ route('website.menus') }}">{{ __('website.menus') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $menu->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!--start menu detail section-->
    <section class="pickup_section secPadding pt-0">
        <div class="container container_start px-lg-0">
            <div class="menu_header mb-4">
                @if($menu->getFirstMediaUrl('menus'))
                    <img src="{{ $menu->getFirstMediaUrl('menus') }}" alt="{{ $menu->name }}" class="menu_image mb-3" style="max-width: 100%; height: auto;">
                @endif
                <h1 class="menu_title mb-3">{{ $menu->name }}</h1>
                @if($menu->content)
                    <div class="menu_content mb-4">
                        {!! $menu->content !!}
                    </div>
                @endif
            </div>

            @if($categories && $categories->count() > 0)
            <!-- Premium Delicate Category Selector -->
            <div class="menu_selector_wrapper">
                <div class="menu_dropdown_label">{{ __('website.select_category') ?? 'Discover Categories' }}</div>

                <div class="custom_dropdown" id="categoryDropdown">
                    <div class="dropdown_trigger" onclick="toggleCategoryDropdown()">
                        <div class="selected_val">
                            @php
                                $settingModel = \App\Models\Setting::getSettingModel();
                                $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
                                // active_category might be high-level or specific
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

            <div class="row">
                <div class="col-12">
                        @if($active_category && isset($active_category['products']) && $active_category['products']->count() > 0)
                            <div class="row incat_row">
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
                                                        $settingModel = \App\Models\Setting::getSettingModel();
                                                        $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
                                                        $productImage = $product->getFirstMediaUrl('products', 'thumb');
                                                        $hasImage = !empty($productImage);
                                                    @endphp
                                                    <img src="{{ $productImage ?: $logoUrl }}"
                                                         class="prodThumb_img {{ !$hasImage ? 'no-product-image' : '' }}"
                                                         alt="{{ $product->name }}"
                                                         class="prodThumb_img">
                                                </a>
                                                <div class="content_box">
                                                    <h5 class="pro_title">
                                                        <a href="{{ route('website.products', $product->slug) }}?from_menu=1">{{ $product->name }}</a>
                                                    </h5>
                                                    <div class="content_bInfo">
                                                        <span class="health_status">{{ __('website.healthy') }}</span>
                                                        @if($showPrice)
                                                            <div class="pro_price">
                                                                @php
                                                                    $priceDetails = $product->getPriceDetails(session('currency'));
                                                                @endphp
                                                                @if($priceDetails['has_discount'])
                                                                    <span class="price-before" style="text-decoration: line-through; color: #999; margin-inline-end: 8px;">
                                                                        {{ number_format($priceDetails['original'], 2) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}
                                                                    </span>
                                                                    <span class="price-after" style="color: #f6d814; font-weight: bold;">
                                                                        {{ number_format($priceDetails['discounted'], 2) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}
                                                                    </span>
                                                                @else
                                                                    {{ number_format($priceDetails['discounted'], 2) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}
                                                                @endif
                                                            </div>
                                                        @endif
                                                        @if($product->calories)
                                                            <div class="kcal_flex">
                                                                <i class="kcal_icon"></i>
                                                                <span>{{ $product->calories }}kcal</span>
                                                            </div>
                                                        @endif
                                                        @if($product->definitions && count($product->definitions) > 0)
                                                            <div class="fats_info">
                                                                @foreach($product->definitions as $definition)
                                                                    <div class="carbIN_flex">
                                                                        <span>{{ $definition->name }}</span>
                                                                        <strong>{{ rtrim(rtrim(number_format((float)$definition->pivot->value, 2, '.', ''), '0'), '.') }}{{ $definition->unit }}</strong>
                                                                    </div>
                                                                @endforeach
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
                            <div class="alert alert-info">
                                <p>{{ __('website.no_products_in_category') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    <h4>{{ __('website.no_products_in_menu') }}</h4>
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
    </script>
@endsection

