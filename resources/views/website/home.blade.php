@extends('website.layout.master')
@section('title', __('website.home'))
@section('body', false)
@section('website-main')
    <!-- Content -->
    <div class="main_bodyTop">
        <!--start intro section-->
        @if ($showSliderSectionDesktop || $showSliderSectionMobile)
            @if (count($sliders) > 0)
                <section
                    class="intro__section orderMD_two @if (!$showSliderSectionDesktop && $showSliderSectionMobile) d-lg-none @elseif($showSliderSectionDesktop && !$showSliderSectionMobile) d-none d-lg-block @endif">
                    <div class="container">
                        <div class="row">
                            <div class="col-12 col-lg-12">
                                <div class="intro_slider mb-0">
                                    @foreach ($sliders as $slider)
                                        <div class="ontop_item">
                                            @if ($slider->url)
                                                <a href="{{ $slider->url }}" class="d-block w-100 h-100">
                                            @endif
                                            <img src="{{ $slider->localized_image_url ?: asset('website/assets/img/bk.png') }}" alt=""
                                                class="absTop_cover" loading="eager" fetchpriority="high" decoding="async">
                                            @if ($slider->url)
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        @endif

        <!--start delivery section-->
        <section class="delivery_section orderMD_one">
            <div class="container">
                <div class="width_66 mx-lg-auto">
                    <ul class="nav nav-pills delivery_pills">
                        <li class="nav-item @if(!$hasPickupProducts) d-none @endif">
                            <a class="nav-link delivery_cardN {{ session('menu_type') == 'delivery' ? ' active' : '' }}"
                                href="{{ url('menu_type?type=delivery') }}">
                                <span class="delivery_titleN">{{ __('website.delivery') }}</span>
                            </a>
                        </li>
                        <li class="nav-item @if(!$hasPickupProducts) d-none @endif">
                            <a class="nav-link delivery_cardN {{ session('menu_type') == 'pickup' ? 'active' : '' }} {{ !$hasPickupProducts ? 'disabled' : '' }}"
                                href="{{ $hasPickupProducts ? url('menu_type?type=pickup') : 'javascript:void(0)' }}"
                                @if (!$hasPickupProducts) onclick="return false;" @endif>
                                <span class="delivery_titleN">{{ __('website.pick_up') }}</span>
                                @if (!$hasPickupProducts)
                                    <small class="delivery_unavailable">{{ __('website.unavailable') }}</small>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Pickup Branch Selection Section -->
        @if (session('menu_type') == 'pickup')
            @php
                $userLocation = session('user_location');
                $cityId = $userLocation['city_id'] ?? null;
                $selectedBranchId = session('pickup_branch_id');
                $branches = collect([]);
                $selectedBranch = null;

                if ($cityId) {
                    $branches = \App\Models\Branch::where('active', 1)
                        ->whereHas('cities', function ($q) use ($cityId) {
                            $q->where('locations.id', $cityId);
                        })
                        ->orderBy('name')
                        ->get();

                    if ($selectedBranchId) {
                        $selectedBranch = $branches->where('id', $selectedBranchId)->first();
                    }
                }
            @endphp

            <section class="pickup_branch_section orderMD_one" id="pickup-branch-section"
                style="{{ !$cityId ? 'display:none;' : '' }}">
                <div class="container">
                    <div class="width_66 mx-lg-auto">
                        <div class="pickup_branch_wrapper">
                            <h4 class="mb-3">{{ __('website.select_pickup_branch') ?? 'اختر فرع الاستلام' }}</h4>

                            @if ($selectedBranch)
                                <div class="alert alert-info mb-3" id="selected-branch-info">
                                    <strong>{{ __('website.selected_branch') ?? 'الفرع المختار' }}:</strong>
                                    <div class="mt-2">
                                        <strong>{{ $selectedBranch->name }}</strong><br>
                                        <small>{{ $selectedBranch->address }}</small>
                                    </div>
                                </div>
                            @endif

                            <div class="formSc_group formSc_home">
                                <select id="home_pickup_branch_id" class="datePick_input" style="width: 100%;">
                                    <option value="">{{ __('website.choose_branch') ?? 'اختر الفرع' }}</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ $selectedBranch && $selectedBranch->id == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="branches-loading-home" style="display:none;" class="mt-2">
                                <small class="text-muted">{{ __('website.loading') ?? 'جاري التحميل...' }}</small>
                            </div>

                            <div id="no-branches-message-home" style="display:none;" class="alert alert-warning mt-2">
                                {{ __('website.no_branches_available_for_city') ?? 'لا توجد فروع متاحة في هذه المدينة' }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>

    <!-- Fixed Categories Bar (appears on scroll) -->
    @if (count($categories) > 0)
        <div class="fixed-categories-bar" id="fixedCategoriesBar" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
            <div class="container px-lg-0">
                <div class="swiper categoriesSwiperFixed" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <ul class="asideCat_list swiper-wrapper">
                        @foreach ($categories as $index => $category)
                            <li class="swiper-slide">
                                <a href="#category-{{ $category->slug }}"
                                    class="asideCat_link fixed-cat-link {{ $index == 0 ? 'active_catlink' : '' }}"
                                    data-category-slug="{{ $category->slug }}">
                                    <span> {{ $category->name }} </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Categories and Products Section -->
    @if (count($categories) > 0)
        <section class="pickup_section" id="categoriesSection">
            <div class="container-fluid">
                <div class="row">
                    <!-- Categories Sidebar (Mobile style for both mobile and desktop) -->
                    <div class="col-12 p-0">
                        <div class="asideCat_column">
                            <div class="swiper categoriesSwiper" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <ul class="asideCat_list swiper-wrapper">
                                    @foreach ($categories as $index => $category)
                                        <li class="swiper-slide">
                                            <a href="#category-{{ $category->slug }}"
                                                class="asideCat_link {{ $index == 0 ? 'active_catlink' : '' }}"
                                                data-category-slug="{{ $category->slug }}">
                                                @php
                                                    $categoryImage = $category->image_url;
                                                    $hasCategoryImage = !empty($categoryImage);
                                                @endphp
                                                <img src="{{ $categoryImage ?: $logoUrl }}" alt="{{ $category->name }}"
                                                    class="aslidCat_img {{ !$hasCategoryImage ? 'no-category-image' : '' }}"
                                                    loading="lazy" decoding="async">
                                                <span> {{ \Illuminate\Support\Str::limit($category->name, 9) }} </span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--start banner section-->
        @if ($showBannerSection && $banners->count() > 0)
            <section class="banner_section">
                <div class="banner_slider_container">
                    <div class="banner_slider">
                        @foreach ($banners as $banner)
                            <div class="banner_slide">
                                @if ($banner->link)
                                    <a href="{{ $banner->link }}" class="banner_link">
                                @endif
                                <div class="banner_container">
                                    <div class="banner_image_wrapper">
                                        @if ($banner->image_url)
                                            <img src="{{ $banner->image_url }}"
                                                alt="{{ $banner->localized_title ?? 'Banner' }}" class="banner_full_image"
                                                loading="lazy" decoding="async">
                                        @endif
                                    </div>
                                </div>
                                @if ($banner->link)
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
        <!-- Products for each category -->
        @foreach ($categories as $index => $category)
            @php
                $categoryProducts = $category->products ?? collect();
            @endphp
            @if ($categoryProducts->count() > 0)
                @if ($index > 0)
                    <div class="category-separator"></div>
                @endif
                <section id="category-{{ $category->slug }}" class="pickup_section pt-0">
                    <div class="container px-lg-0">
                        <div class="between_flex titleMrg_bttom">
                            <div class="title_wrapper">
                                <h3>{{ $category->name }}</h3>
                            </div>
                            <a href="{{ route('website.categories', $category->slug) }}"
                                class="more_link">{{ __('website.see_all') }}</a>
                        </div>
                        <div class="row incat_row">
                            @foreach ($categoryProducts as $product)
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
                        </div>
                    </div>
                </section>
            @endif
        @endforeach
    @else
        <section class="pickup_section secPadding">
            <div class="container px-lg-0">
                <h5 class="alert alert-warning border-none rounded-0 text-center py-5">
                    {{ __('website.no_categories_in_this_type') }}
                </h5>
            </div>
        </section>
    @endif

@endsection

@section('website-footer')
    <script>
        $(document).ready(function() {
            if ($('.banner_slider').length) {
                var is_rtl = $("html").attr("dir") === "rtl" || $("html[lang='ar']").length > 0;
                var bannerCount = $('.banner_slider .banner_slide').length;

                $('.banner_slider').slick({
                    dots: false,
                    infinite: bannerCount > 1,
                    speed: 500,
                    autoplay: bannerCount > 1,
                    autoplaySpeed: 5000,
                    arrows: false,
                    rtl: is_rtl,
                    pauseOnHover: true,
                    pauseOnFocus: true,
                    slidesToShow: bannerCount === 1 ? 1 : (window.innerWidth >= 992 ? 3 : 2),
                    slidesToScroll: 1,
                    centerMode: bannerCount === 1,
                    centerPadding: bannerCount === 1 ? '0' : '0',
                    responsive: [
                        {
                            breakpoint: 991,
                            settings: {
                                slidesToShow: bannerCount === 1 ? 1 : 2,
                                slidesToScroll: 1,
                                centerMode: bannerCount === 1,
                                centerPadding: bannerCount === 1 ? '0' : '0',
                            }
                        }
                    ]
                });

                // Add data attribute for CSS targeting
                $('.banner_slider').attr('data-banner-count', bannerCount);
            }

            var categoriesSwiper = null;
            var categoriesSwiperFixed = null;

            if (typeof Swiper !== 'undefined' && $('.categoriesSwiper').length) {
                var is_rtl = $("html").attr("dir") === "rtl" || $("html[lang='ar']").length > 0;
                var isDesktop = $(window).width() >= 992;

                if (isDesktop) {
                    categoriesSwiper = new Swiper('.categoriesSwiper', {
                        slidesPerView: 'auto',
                        spaceBetween: 14,
                        freeMode: true,
                        grabCursor: true,
                        touchEventsTarget: 'container',
                        direction: 'horizontal',
                        rtl: is_rtl,
                        resistance: true,
                        resistanceRatio: 0.85,
                        breakpoints: {
                            992: {
                                slidesPerView: 'auto',
                                spaceBetween: 14,
                            }
                        }
                    });
                } else {
                    $('.categoriesSwiper').removeClass('swiper');
                    $('.categoriesSwiper .swiper-wrapper').removeClass('swiper-wrapper');
                    $('.categoriesSwiper .swiper-slide').removeClass('swiper-slide');
                }
            }

            if (typeof Swiper !== 'undefined' && $('.categoriesSwiperFixed').length) {
                var is_rtl = $("html").attr("dir") === "rtl" || $("html[lang='ar']").length > 0;
                var isDesktop = $(window).width() >= 992;

                if (isDesktop) {
                    categoriesSwiperFixed = new Swiper('.categoriesSwiperFixed', {
                        slidesPerView: 'auto',
                        spaceBetween: 14,
                        freeMode: true,
                        grabCursor: true,
                        touchEventsTarget: 'container',
                        direction: 'horizontal',
                        rtl: is_rtl,
                        resistance: true,
                        resistanceRatio: 0.85,
                        breakpoints: {
                            992: {
                                slidesPerView: 'auto',
                                spaceBetween: 14,
                            }
                        }
                    });
                } else {
                    $('.categoriesSwiperFixed').removeClass('swiper');
                    $('.categoriesSwiperFixed .swiper-wrapper').removeClass('swiper-wrapper');
                    $('.categoriesSwiperFixed .swiper-slide').removeClass('swiper-slide');
                }
            }

            var categoriesSection = $('#categoriesSection');
            var fixedCategoriesBar = $('#fixedCategoriesBar');
            var categorySections = [];

            @foreach ($categories as $category)
                categorySections.push({
                    slug: '{{ $category->slug }}',
                    element: $('#category-{{ $category->slug }}')
                });
            @endforeach

            function updateActiveCategory() {
                var scrollTop = $(window).scrollTop();
                var categoriesSectionTop = categoriesSection.offset().top;
                var categoriesSectionHeight = categoriesSection.outerHeight();
                var windowHeight = $(window).height();

                if (scrollTop > categoriesSectionTop + categoriesSectionHeight) {
                    fixedCategoriesBar.addClass('visible');
                    $('body').addClass('fixed-categories-visible');
                } else {
                    fixedCategoriesBar.removeClass('visible');
                    $('body').removeClass('fixed-categories-visible');
                }

                var activeCategory = null;
                var offset = 150;

                for (var i = categorySections.length - 1; i >= 0; i--) {
                    var section = categorySections[i].element;
                    if (section.length && section.offset().top <= scrollTop + offset) {
                        activeCategory = categorySections[i].slug;
                        break;
                    }
                }

                if (activeCategory) {
                    $('.asideCat_link').removeClass('active_catlink');
                    $('.asideCat_link[data-category-slug="' + activeCategory + '"]').addClass('active_catlink');

                    scrollToActiveCategory(activeCategory);
                    scrollToActiveCategoryFixed(activeCategory);
                }
            }

            function scrollToActiveCategory(activeCategorySlug) {
                var $activeLink = $('.categoriesSwiper .asideCat_link[data-category-slug="' + activeCategorySlug +
                    '"]');
                if ($activeLink.length) {
                    var $categoriesList = $('.categoriesSwiper .asideCat_list');

                    if (categoriesSwiper) {
                        var $activeSlide = $activeLink.closest('.swiper-slide');
                        var activeIndex = $activeSlide.index();
                        if (activeIndex >= 0) {
                            categoriesSwiper.slideTo(activeIndex, 300);
                        }
                    } else {
                        var $activeItem = $activeLink.closest('li');
                        if ($activeItem.length === 0) {
                            $activeItem = $activeLink.parent();
                        }

                        if ($activeItem.length && $categoriesList.length) {
                            var listElement = $categoriesList[0];
                            var itemElement = $activeItem[0];
                            var listRect = listElement.getBoundingClientRect();
                            var itemRect = itemElement.getBoundingClientRect();
                            var isItemVisible = itemRect.left >= listRect.left && itemRect.right <= listRect.right;

                            if (!isItemVisible) {
                                var itemLeft = itemElement.offsetLeft;
                                var itemWidth = itemElement.offsetWidth;
                                var listWidth = listElement.clientWidth;
                                var currentScroll = listElement.scrollLeft;
                                var targetScroll = itemLeft - (listWidth / 2) + (itemWidth / 2);
                                var maxScroll = listElement.scrollWidth - listElement.clientWidth;
                                targetScroll = Math.max(0, Math.min(targetScroll, maxScroll));

                                if (listElement.scrollTo) {
                                    listElement.scrollTo({
                                        left: targetScroll,
                                        behavior: 'smooth'
                                    });
                                } else {
                                    $categoriesList.animate({
                                        scrollLeft: targetScroll
                                    }, 300);
                                }
                            }
                        }
                    }
                }
            }

            function scrollToActiveCategoryFixed(activeCategorySlug) {
                var $activeLink = $('.categoriesSwiperFixed .asideCat_link[data-category-slug="' +
                    activeCategorySlug + '"]');
                if ($activeLink.length) {
                    var $categoriesList = $('.categoriesSwiperFixed .asideCat_list');

                    if (categoriesSwiperFixed) {
                        var $activeSlide = $activeLink.closest('.swiper-slide');
                        var activeIndex = $activeSlide.index();
                        if (activeIndex >= 0) {
                            categoriesSwiperFixed.slideTo(activeIndex, 300);
                        }
                    } else {
                        var $activeItem = $activeLink.closest('li');
                        if ($activeItem.length === 0) {
                            $activeItem = $activeLink.parent();
                        }

                        if ($activeItem.length && $categoriesList.length) {
                            var listElement = $categoriesList[0];
                            var itemElement = $activeItem[0];
                            var listRect = listElement.getBoundingClientRect();
                            var itemRect = itemElement.getBoundingClientRect();
                            var isItemVisible = itemRect.left >= listRect.left && itemRect.right <= listRect.right;

                            if (!isItemVisible) {
                                var itemLeft = itemElement.offsetLeft;
                                var itemWidth = itemElement.offsetWidth;
                                var listWidth = listElement.clientWidth;
                                var currentScroll = listElement.scrollLeft;
                                var targetScroll = itemLeft - (listWidth / 2) + (itemWidth / 2);
                                var maxScroll = listElement.scrollWidth - listElement.clientWidth;
                                targetScroll = Math.max(0, Math.min(targetScroll, maxScroll));

                                if (listElement.scrollTo) {
                                    listElement.scrollTo({
                                        left: targetScroll,
                                        behavior: 'smooth'
                                    });
                                } else {
                                    $categoriesList.animate({
                                        scrollLeft: targetScroll
                                    }, 300);
                                }
                            }
                        }
                    }
                }
            }

            var scrollTimeout;
            $(window).on('scroll', function() {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(updateActiveCategory, 10);
            });

            updateActiveCategory();

            $(window).on('resize', function() {
                var isDesktop = $(window).width() >= 992;

                if (isDesktop && !categoriesSwiper && $('.categoriesSwiper').length) {
                    var is_rtl = $("html").attr("dir") === "rtl" || $("html[lang='ar']").length > 0;
                    categoriesSwiper = new Swiper('.categoriesSwiper', {
                        slidesPerView: 'auto',
                        spaceBetween: 14,
                        freeMode: true,
                        grabCursor: true,
                        touchEventsTarget: 'container',
                        direction: 'horizontal',
                        rtl: is_rtl,
                        resistance: true,
                        resistanceRatio: 0.85
                    });
                } else if (!isDesktop && categoriesSwiper) {
                    categoriesSwiper.destroy(true, true);
                    categoriesSwiper = null;
                    $('.categoriesSwiper').removeClass('swiper');
                    $('.categoriesSwiper .swiper-wrapper').removeClass('swiper-wrapper');
                    $('.categoriesSwiper .swiper-slide').removeClass('swiper-slide');
                }

                if (isDesktop && !categoriesSwiperFixed && $('.categoriesSwiperFixed').length) {
                    var is_rtl = $("html").attr("dir") === "rtl" || $("html[lang='ar']").length > 0;
                    categoriesSwiperFixed = new Swiper('.categoriesSwiperFixed', {
                        slidesPerView: 'auto',
                        spaceBetween: 14,
                        freeMode: true,
                        grabCursor: true,
                        touchEventsTarget: 'container',
                        direction: 'horizontal',
                        rtl: is_rtl,
                        resistance: true,
                        resistanceRatio: 0.85
                    });
                } else if (!isDesktop && categoriesSwiperFixed) {
                    categoriesSwiperFixed.destroy(true, true);
                    categoriesSwiperFixed = null;
                    $('.categoriesSwiperFixed').removeClass('swiper');
                    $('.categoriesSwiperFixed .swiper-wrapper').removeClass('swiper-wrapper');
                    $('.categoriesSwiperFixed .swiper-slide').removeClass('swiper-slide');
                }
            });

            $(document).on('click', '.asideCat_link', function(e) {
                e.preventDefault();
                const categorySlug = $(this).data('category-slug');
                const targetId = '#category-' + categorySlug;
                const $target = $(targetId);

                if ($target.length) {
                    $('.asideCat_link').removeClass('active_catlink');
                    $('.asideCat_link[data-category-slug="' + categorySlug + '"]').addClass(
                        'active_catlink');

                    scrollToActiveCategory(categorySlug);
                    scrollToActiveCategoryFixed(categorySlug);

                    $('html, body').animate({
                        scrollTop: $target.offset().top - 100
                    }, 800);
                }
            });

            if (window.location.hash) {
                const hash = window.location.hash;
                const $target = $(hash);
                if ($target.length) {
                    setTimeout(function() {
                        $('html, body').animate({
                            scrollTop: $target.offset().top - 100
                        }, 800);
                        const categorySlug = hash.replace('#category-', '');
                        $('.asideCat_link[data-category-slug="' + categorySlug + '"]').addClass(
                            'active_catlink');
                        scrollToActiveCategory(categorySlug);
                        scrollToActiveCategoryFixed(categorySlug);
                    }, 500);
                }
            }
            $(document).on('click', '.absAdd_fav', function(e) {
                e.preventDefault();
                e.stopPropagation();

                @auth('web')
                    var button = $(this);
                    var productId = button.attr('data-product-id') || button.data('product-id');
                    var isFavorited = button.hasClass('favorited');

                    if (!productId) {
                        console.error('Product ID is undefined or null');
                        AppSwal.error('Product ID is missing. Value: ' + productId);
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
                        },
                        error: function(xhr) {
                            AppSwal.error('{{ __("website.error_processing_favorite") }}');
                        }
                    });
                @else
                    AppSwal.confirm({
                        title: '{{ __("website.login_required") }}',
                        text: '{{ __("website.please_login_to_add_favorites") }}',
                        confirmButtonText: '{{ __("website.login") }}',
                        cancelButtonText: '{{ __("website.cancel") }}'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '{{ route('website.login') }}';
                        }
                    });
                @endauth
            });


            $(document).on('click', '.quick-add-to-cart', function(e) {
                e.preventDefault();

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

                $button.addClass('disabled').text('{{ __('website.adding') }}...');

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
                        console.log('Quick Add to Cart Response:', response);

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

                            // Get product info for spinner data attributes
                            const $productCard = $button.closest(
                                '.trend_cardN1, .product_cardN1, .productOne_itemN');
                            const $productLink = $productCard.find('a[href*="/products/"]');
                            const productSlug = $productLink.attr('href') ? $productLink.attr(
                                'href').split('/products/')[1] : '';
                            const hasAddons = $button.data('has-addons') || '0';

                            // Replace buttons wrapper with number spinner
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

                            // Replace the entire buttons wrapper
                            if ($buttonsWrapper.length > 0) {
                                $buttonsWrapper.replaceWith(spinnerHtml);
                            } else {
                                // Fallback: replace just the button
                                $button.replaceWith(spinnerHtml);
                            }

                            // Spinner events are already set up with event delegation, no need to reinitialize

                            // Update cart total in sidebar
                            if (response.cart_total && $('.total_box').length) {
                                $('.total_box').html('<strong>Total:</strong> ' + response
                                    .cart_total + ' ' + (response.currency ||
                                        '{{ \App\Models\Currency::getCurrentCurrencySign() }}'
                                    ));
                            }

                            // Refresh cart sidebar
                            if ($('#sideCart_menu .cartSide_content').length) {
                                $('#sideCart_menu .cartSide_content').load(window.location
                                    .href + " #sideCart_menu .cartSide_content > *");
                            }

                            AppSwal.success('{{ __("website.added_to_cart") }}');
                        } else {
                            AppSwal.error(
                                response.message ||
                                '{{ __('website.something_went_wrong') }}',
                                '{{ __('website.error') }}'
                            );
                            $button.removeClass('disabled').html(originalButtonHtml);
                        }
                    },
                    error: function(xhr) {
                        console.error('Quick Add to Cart Error:', xhr);
                        let errorMessage = '{{ __('website.something_went_wrong') }}';

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON
                            .message) {
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

            // Initialize spinner events (separate function for reuse)
            function initializeSpinnerEvents() {
                // Use event delegation for dynamically added spinners
                // This ensures spinners added after page load also work
                $(document).off('click', '.number__spinner .ns-btn a');
                $(document).on('click', '.number__spinner .ns-btn a', handleSpinnerClick);
            }

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

                if (direction === 'up') {
                    newQty = currentQty + 1;
                } else if (direction === 'dwn') {
                    // If quantity is 1, remove from cart; otherwise decrement
                    if (currentQty === 1) {
                        removeProductFromCart($spinner, productId);
                        return;
                    }
                    newQty = Math.max(1, currentQty - 1);
                }

                if (!productId) {
                    AppSwal.error('{{ __('website.invalid_product') }}');
                    return;
                }

                // Mark as updating to prevent duplicate calls
                $spinner.addClass('updating');

                // Disable buttons during update
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
                            // Update quantity display
                            $input.val(newQty);

                            // Update cart count badges
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

                            // Update cart total in sidebar
                            if (response.total && $('.total_box').length) {
                                $('.total_box').html('<strong>Total:</strong> ' + response.total + ' ' +
                                    (response.currency ||
                                        '{{ \App\Models\Currency::getCurrentCurrencySign() }}'));
                            }

                            // Refresh cart sidebar
                            if ($('#sideCart_menu .cartSide_content').length) {
                                $('#sideCart_menu .cartSide_content').load(window.location.href +
                                    " #sideCart_menu .cartSide_content > *");
                            }

                            // Show success toast (optional)
                            AppSwal.success('{{ __('website.cart_updated') }}');
                        } else {
                            AppSwal.error(response.message, '{{ __('website.error') }}');
                        }

                        // Re-enable buttons
                        $spinner.find('.ns-btn a').removeClass('disabled');
                        $input.removeClass('loading');
                        $spinner.removeClass('updating');
                    },
                    error: function(xhr) {
                        let errorMessage = '{{ __('website.something_went_wrong') }}';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        AppSwal.error(errorMessage);

                        // Re-enable buttons
                        $spinner.find('.ns-btn a').removeClass('disabled');
                        $input.removeClass('loading');
                        $spinner.removeClass('updating');
                    }
                });
            }

            // Function to remove product from cart
            function removeProductFromCart($spinner, productId) {
                // Disable buttons during removal
                $spinner.find('.ns-btn a').addClass('disabled');
                $spinner.find('.pl-ns-value').addClass('loading');

                $.ajax({
                    url: "{{ route('cart.remove') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        product_id: productId
                    },
                    success: function(response) {
                        if (response.status) {
                            // Get product info from spinner data attributes
                            const productSlug = $spinner.data('product-slug') || '';
                            const hasAddons = $spinner.data('has-addons') === '1' || $spinner.data(
                                'has-addons') === 1;

                            // Replace spinner with buttons
                            let buttonsHtml = '<div class="buttons_wrapper w-100 product-buttons-' +
                                productId + '">';

                            if (hasAddons) {
                                buttonsHtml += '<a href="{{ url('/products') }}/' + productSlug +
                                    '" class="main_bttn hvr-sweep-to-right">{{ __('website.add_to_cart') }}</a>';
                            } else {
                                buttonsHtml +=
                                    '<a href="javascript:void(0)" class="main_bttn hvr-sweep-to-right quick-add-to-cart" data-product-id="' +
                                    productId +
                                    '" data-has-addons="0">{{ __('website.add_to_cart') }}</a>';
                            }
                            buttonsHtml += '<a href="{{ url('/products') }}/' + productSlug +
                                '" class="main_bttn white_bttn hvr-sweep-to-right"> {{ __('website.buy_now') }} </a>';
                            buttonsHtml += '</div>';

                            $spinner.replaceWith(buttonsHtml);

                            // Update cart count badges
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

                            // Update cart total in sidebar
                            if (response.total && $('.total_box').length) {
                                $('.total_box').html('<strong>Total:</strong> ' + response.total + ' ' +
                                    (response.currency ||
                                        '{{ \App\Models\Currency::getCurrentCurrencySign() }}'));
                            }

                            // Refresh cart sidebar
                            if ($('#sideCart_menu .cartSide_content').length) {
                                $('#sideCart_menu .cartSide_content').load(window.location.href +
                                    " #sideCart_menu .cartSide_content > *");
                            }

                            // Show success toast
                            AppSwal.success('{{ __('website.removed_from_cart') }}');
                        } else {
                            AppSwal.error(response.message ||
                                '{{ __('website.something_went_wrong') }}');
                            $spinner.find('.ns-btn a').removeClass('disabled');
                            $spinner.find('.pl-ns-value').removeClass('loading');
                        }
                    },
                    error: function(xhr) {
                        AppSwal.error('{{ __('website.something_went_wrong') }}');
                        $spinner.find('.ns-btn a').removeClass('disabled');
                        $spinner.find('.pl-ns-value').removeClass('loading');
                    }
                });
            }

            // Initialize spinner events on page load
            initializeSpinnerEvents();

            // Pickup Branch Selection
            @if (session('menu_type') == 'pickup')
                let menuType = '{{ session('menu_type', 'delivery') }}';

                // Load branches when city is selected (after location is saved)
                function loadBranchesForPickup(skipChangeTrigger = false) {
                    const userLocation = @json(session('user_location'));
                    const cityId = userLocation ? userLocation.city_id : null;
                    const pickupBranchSection = $('#pickup-branch-section');
                    const branchSelect = $('#home_pickup_branch_id');
                    const loadingDiv = $('#branches-loading-home');
                    const noBranchesDiv = $('#no-branches-message-home');
                    // Don't cache selectedBranchInfo - get it fresh each time

                    if (menuType === 'pickup' && cityId) {
                        // Show section
                        pickupBranchSection.slideDown();

                        // Show loading
                        loadingDiv.show();
                        noBranchesDiv.hide();
                        branchSelect.prop('disabled', true);

                        // Load branches by city
                        $.ajax({
                            url: "{{ route('website.branches.by-city') }}",
                            method: 'GET',
                            data: {
                                city_id: cityId
                            },
                            success: function(response) {
                                loadingDiv.hide();

                                if (response.status && response.branches && response.branches.length >
                                    0) {
                                    // Clear and populate branch select
                                    branchSelect.empty().append(
                                        '<option value="">{{ __('website.choose_branch') ?? 'اختر الفرع' }}</option>'
                                    );

                                    response.branches.forEach(function(branch) {
                                        branchSelect.append(
                                            `<option value="${branch.id}">${branch.name}</option>`
                                        );
                                    });

                                    // Check if there's a saved branch
                                    const savedBranchId = '{{ session('pickup_branch_id') }}';

                                    if (savedBranchId) {
                                        // Convert to number for comparison
                                        const savedBranchIdNum = parseInt(savedBranchId);
                                        const savedBranch = response.branches.find(b => parseInt(b
                                            .id) === savedBranchIdNum);

                                        if (savedBranch) {
                                            // Set the select value
                                            branchSelect.val(savedBranchIdNum);

                                            // Trigger change event to update any listeners (only if not initial load)
                                            if (!skipChangeTrigger) {
                                                branchSelect.trigger('change');
                                            }

                                            // Update or show branch info - refresh the selector
                                            let selectedBranchInfo = $('#selected-branch-info');

                                            // Create branch info HTML
                                            const branchInfoHtml = `
                                                <strong>{{ __('website.selected_branch') ?? 'الفرع المختار' }}:</strong>
                                                <div class="mt-2">
                                                    <strong>${savedBranch.name}</strong><br>
                                                    <small>${savedBranch.address || ''}</small>
                                                </div>
                                            `;

                                            if (selectedBranchInfo.length > 0) {
                                                // Update existing branch info
                                                selectedBranchInfo.html(branchInfoHtml).show();
                                            } else {
                                                // Create branch info if it doesn't exist
                                                const branchWrapper = branchSelect.closest(
                                                    '.pickup_branch_wrapper');
                                                if (branchWrapper.length > 0) {
                                                    // Insert before the select element's parent (formSc_group)
                                                    branchSelect.closest('.formSc_group').before(`
                                                        <div class="alert alert-info mb-3" id="selected-branch-info">
                                                            ${branchInfoHtml}
                                                        </div>
                                                    `);
                                                }
                                            }
                                        } else {
                                            // Hide branch info if branch not found
                                            const selectedBranchInfo = $('#selected-branch-info');
                                            if (selectedBranchInfo.length > 0) {
                                                selectedBranchInfo.hide();
                                            }
                                        }
                                    } else {
                                        // Hide branch info if no branch is selected
                                        const selectedBranchInfo = $('#selected-branch-info');
                                        if (selectedBranchInfo.length > 0) {
                                            selectedBranchInfo.hide();
                                        }
                                    }

                                    branchSelect.prop('disabled', false);
                                    noBranchesDiv.hide();
                                } else {
                                    branchSelect.empty().append(
                                        '<option value="">{{ __('website.no_branches_available') }}</option>'
                                    );
                                    branchSelect.prop('disabled', true);
                                    noBranchesDiv.show();
                                    selectedBranchInfo.hide();
                                }
                            },
                            error: function() {
                                loadingDiv.hide();
                                branchSelect.prop('disabled', false);
                                AppSwal.error('{{ __('website.something_went_wrong') }}');
                            }
                        });
                    } else {
                        pickupBranchSection.slideUp();
                    }
                }

                // Listen for branch update from location modal
                window.addEventListener('pickupBranchUpdated', function(event) {
                    const branchId = event.detail.branchId;
                    if (branchId && menuType === 'pickup') {
                        // Reload branches to get the updated list
                        loadBranchesForPickup();
                    }
                });

                // Flag to prevent showing success message on page load
                let isInitialLoad = true;

                // Save branch to session when selected
                $('#home_pickup_branch_id').on('change', function() {
                    const branchId = $(this).val();
                    const branchSelect = $(this);
                    const selectedBranchInfo = $('#selected-branch-info');

                    if (!branchId) {
                        selectedBranchInfo.hide();
                        return;
                    }

                    // Skip showing success message if this is initial load
                    if (isInitialLoad) {
                        isInitialLoad = false;
                        return;
                    }

                    // Save branch to session
                    $.ajax({
                        url: "{{ route('website.branches.save-pickup') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            branch_id: branchId
                        },
                        success: function(response) {
                            if (response.status && response.branch) {
                                selectedBranchInfo.html(`
                                    <strong>{{ __('website.selected_branch') ?? 'الفرع المختار' }}:</strong>
                                    <div class="mt-2">
                                        <strong>${response.branch.name}</strong><br>
                                        <small>${response.branch.address}</small>
                                    </div>
                                `).show();

                                AppSwal.success(response.message ||
                                    '{{ __("website.branch_selected_successfully") ?? "تم اختيار الفرع بنجاح" }}',
                                    '{{ __("website.success") }}');
                            }
                        },
                        error: function(xhr) {
                            AppSwal.error(xhr.responseJSON.message || '{{ __("website.something_went_wrong") }}');
                            branchSelect.val('');
                        }
                    });
                });

                // Load branches on page load if city is selected
                $(document).ready(function() {
                    // Wait a bit for the page to fully load
                    setTimeout(function() {
                        // Load branches without triggering change event on initial load
                        loadBranchesForPickup(true);
                        // Reset flag after initial load is complete
                        setTimeout(function() {
                            isInitialLoad = false;
                        }, 1000);
                    }, 300);
                });

                // Listen for location change (when modal is saved and page reloads)
                $(window).on('load', function() {
                    setTimeout(function() {
                        // Load branches without triggering change event on initial load
                        loadBranchesForPickup(true);
                        // Reset flag after initial load is complete
                        setTimeout(function() {
                            isInitialLoad = false;
                        }, 1000);

                        // Show selected branch info if exists after reload
                        const savedBranchId = '{{ session('pickup_branch_id') }}';
                        @if ($selectedBranch)
                            if (savedBranchId && savedBranchId == '{{ $selectedBranch->id }}') {
                                const selectedBranchInfo = $('#selected-branch-info');
                                if (selectedBranchInfo.length > 0) {
                                    selectedBranchInfo.show();
                                }
                            }
                        @endif
                    }, 500);
                });
            @endif
        });
    </script>
@endsection
