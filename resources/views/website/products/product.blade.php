@extends('website.layout.master')
@section('title', $product->name)
@section('body', 'bg-white')
@section('website-main')
    <!-- Content -->
    <!--start pickup section-->
    <section class="pickup_section secPadding pt_sm_0 pt-5">
        <div class="container px-lg-0">
            <div class="breadCrumb_section midPadding">
                <div class="container px-lg-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a
                                    href="{{ route('website.home') }}">{{ __('website.home') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="mealCol_wrap col-12 col-lg-8 mx-lg-auto">
                    @php
                        $enableProductNotes = \App\Models\Setting::getValue('enable_product_notes', null, '0') == '1';
                    @endphp
                    @if(!empty($isBox) && $isBox && $boxProductsByTitle->count())
                        <!-- Box Information at Top -->
                        <div class="box-header-section mb-5">
                            <div class="row align-items-center">
                                <div class="col-12 col-md-5 mb-4 mb-md-0">
                                    <div class="box-image-wrapper" style="position: relative; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                                        @auth('web')
                                            <button
                                                class="absAdd_fav{{ $favoriteProductIds->contains($product->id) ? ' favorited' : '' }}"
                                                data-product-id="{{ $product->id }}"
                                                style="position: absolute; top: 15px; right: 15px; border: none; padding: 10px; border-radius: 50%; cursor: pointer; z-index: 10; box-shadow: 0 2px 8px rgba(0,0,0,0.1); background: {{ $favoriteProductIds->contains($product->id) ? '#ff4444' : 'white' }};">
                                                <i class="las la-heart"></i>
                                            </button>
                                        @endauth
                                        <img src="{{ $product->getFirstMediaUrl('products') ?: asset('website/assets/img/box.png') }}"
                                             alt="{{ $product->name }}"
                                             style="width: 100%; height: 300px; object-fit: cover;" loading="eager" fetchpriority="high" decoding="async">
                                    </div>
                                    @if($enableProductNotes && auth('web')->check())
                                        <div class="mt-3">
                                            <a href="javascript:void(0)" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#productNotesModal" data-product-id="{{ $product->id }}" style="color: #f6d814; font-weight: 500;">
                                                <i class="fa fa-sticky-note me-2"></i> {{ __('website.add_note') }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-7">
                                    <h2 style="font-size: 2rem; font-weight: bold; margin-bottom: 15px; color: white;">{{ $product->name }}</h2>
                                    <div class="box-price-section mb-3" style="display: flex; align-items: center; gap: 15px;">
                                        <span style="font-size: 2rem; font-weight: bold;">
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
                                        </span>
                                        @auth('web')
                                            @if(auth('web')->user()->hasRole('admin'))
                                                <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#editPriceModal" data-product-id="{{ $product->id }}">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                            @endif
                                        @endauth
                                    </div>
                                    @if($product->description)
                                        <p style="font-size: 1rem; line-height: 1.6; opacity: 0.9; margin-bottom: 20px;">{{ $product->description }}</p>
                                    @endif
                                    <div class="rate_flex" style="display: flex; gap: 20px; align-items: center;">
                                        <div class="rate_span" style="display: flex; align-items: center; gap: 5px;">
                                            <span style="font-size: 0.9rem;">{{ __('website.rating') }}</span>
                                            <strong style="font-size: 1.1rem;">4.8</strong>
                                            <i class="fa-solid fa-star"></i>
                                        </div>
                                        <div class="rate_span">
                                            <span style="font-size: 0.9rem;">44 {{ __('website.reviews') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Box Addons Section -->
                        @if(count($groupedAddons) > 0)
                            <div class="box-addons-section mb-5">
                                <h3 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 20px; color: #333;">{{ __('website.box_addons') }}</h3>
                                <div class="accordion" id="accordionMainAddons">
                                    @foreach($groupedAddons as $addonGroupName => $groupData)
                                        @php
                                            $addons = $groupData['addons'] ?? collect();
                                            $group = $groupData['group'] ?? null;
                                            $groupSlug = \Illuminate\Support\Str::slug($addonGroupName);
                                            $isMandatory = $group && ($group['is_selection_mandatory'] ?? false);
                                            $maxSelections = $group ? ($group['max_selections'] ?? null) : null;
                                            $minSelections = $group && $isMandatory ? ($group['min_selections'] ?? null) : null;
                                        @endphp
                                        <div class="accordion-item addon-group-item"
                                             style="border-radius: 10px; margin-bottom: 10px; overflow: hidden;"
                                             data-group-slug="{{ $groupSlug }}"
                                             data-is-mandatory="{{ $isMandatory ? '1' : '0' }}"
                                             data-max-selections="{{ $maxSelections ?? '' }}"
                                             data-min-selections="{{ $minSelections ?? '' }}">
                                            <h2 class="accordion-header">
                                                <div class="accordion-button" style="cursor: default;">
                                                    {{ $addonGroupName }}
                                                    <small class="ms-2">
                                                        @if($isMandatory && $minSelections && $maxSelections && $minSelections == $maxSelections)
                                                            ({{ __('website.select_exactly') }} {{ $minSelections }} {{ $minSelections == 1 ? __('website.item') : __('website.items') }})
                                                        @elseif($isMandatory && $minSelections && $maxSelections)
                                                            ({{ __('website.select_minimum') }} {{ $minSelections }} {{ __('website.and_maximum') }} {{ $maxSelections }} {{ __('website.items') }})
                                                        @elseif($maxSelections)
                                                            ({{ __('website.choose_up_to') }} {{ $maxSelections }} {{ __('website.items') }})
                                                        @elseif($isMandatory && $minSelections)
                                                            ({{ __('website.select_minimum') }} {{ $minSelections }} {{ __('website.items') }})
                                                        @endif
                                                    </small>
                                                </div>
                                            </h2>
                                            <div id="collapseMainAddons-{{ $groupSlug }}"
                                                 class="accordion-collapse show">
                                                <div class="accordion-body">
                                                    <div class="checkACC_flex">
                                                        @php
                                                            $addonsCount = $addons->count();
                                                            $maxSelectionsInt = is_numeric($maxSelections) ? (int)$maxSelections : null;
                                                            $minSelectionsInt = is_numeric($minSelections) ? (int)$minSelections : null;
                                                            $isExactOneRequired = $isMandatory && $minSelectionsInt === 1 && $maxSelectionsInt === 1;
                                                            $isSingleRequired = $isMandatory && (
                                                                ($addonsCount === 1) ||
                                                                ($maxSelectionsInt === 1 && $addonsCount > 0)
                                                            ) && !$isExactOneRequired;
                                                        @endphp
                                                        @foreach($addons as $addon)
                                                            <label class="checkACC_label">
                                                                @if($isSingleRequired)
                                                                    <input value="{{ $addon->id }}"
                                                                           data-price="{{ $addon->getCurrentPrice(session('currency')) }}"
                                                                           name="addons[{{ $groupSlug }}]"
                                                                           type="radio"
                                                                           class="checkACC_box addon-radio"
                                                                           data-group-slug="{{ $groupSlug }}"
                                                                           checked>
                                                                @else
                                                                    <input value="{{ $addon->id }}"
                                                                           data-price="{{ $addon->getCurrentPrice(session('currency')) }}"
                                                                           name="addons[]"
                                                                           type="checkbox"
                                                                           class="checkACC_box addon-checkbox"
                                                                           data-group-slug="{{ $groupSlug }}">
                                                                @endif
                                                                <span class="checkACC_text">
                                                                    {{ $addon->name }} ({{ $addon->getCurrentPrice(session('currency')) }} {{ \App\Models\Currency::getCurrentCurrencySign() }})
                                                                </span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Products Grouped by Title Section -->
                        <div class="box-products-section mb-5">
                            <h3 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 30px; color: #333;">{{ __('website.select_products') }}</h3>

                            @foreach($boxProductsByTitle as $titleIndex => $titleGroup)
                                @php
                                    $titleText = !empty($titleGroup['title']) && is_array($titleGroup['title'])
                                        ? ($titleGroup['title'][app()->getLocale()] ?? $titleGroup['title']['en'] ?? $titleGroup['title']['ar'] ?? '')
                                        : '';
                                    $isRequired = $titleGroup['is_required'] ?? false;
                                    $maxCount = $titleGroup['max_count'] ?? 1;
                                    $minCount = $titleGroup['min_count'] ?? 0;
                                    $products = $titleGroup['products'] ?? collect();
                                @endphp

                                <div class="title-group-card mb-4 {{ $isRequired ? 'required' : '' }}" data-title-index="{{ $titleIndex }}">
                                    <div class="title-header mb-4" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                        <h4 style="font-size: 1.3rem; font-weight: bold; color: #333; margin: 0;">
                                            {{ $titleText ?: __('website.products') }}
                                            @if($isRequired)
                                                <span class="badge bg-danger ms-2" style="font-size: 0.8rem;">{{ __('website.required') }}</span>
                                            @endif
                                        </h4>
                                        <span style="font-size: 0.9rem; color: #666;">
                                            @if($isRequired && $minCount > 0)
                                                @if($minCount == $maxCount)
                                                    {{ __('website.select_exactly') }} {{ $minCount }} {{ $minCount == 1 ? __('website.item') : __('website.items') }}
                                                @else
                                                    {{ __('website.select_minimum') }} {{ $minCount }} {{ __('website.and_maximum') }} {{ $maxCount }} {{ __('website.items') }}
                                                @endif
                                            @elseif($maxCount == 1)
                                                {{ __('website.select_one') }}
                                            @else
                                                {{ __('website.select_up_to') }} {{ $maxCount }} {{ __('website.items') }}
                                            @endif
                                        </span>
                                    </div>

                                    <div class="products-grid box-products-grid">
                                        @foreach($products as $boxProduct)
                                            <div class="product-card box-product-card">
                                                <label class="product-image-wrapper" style="position: relative; display: block; cursor: pointer; width: 100%; margin-bottom: 15px;">
                                                    <div class="product-image" style="width: 100%; height: 200px; border-radius: 10px; overflow: hidden; background: #f0f0f0; position: relative;">
                                                        @php
                                                            $boxProductImage = $boxProduct->getFirstMediaUrl('products');
                                                            $hasBoxImage = !empty($boxProductImage);
                                                        @endphp
                                                        <img src="{{ $boxProductImage ?: $logoUrl }}"
                                                             alt="{{ $boxProduct->name }}"
                                                             class="{{ !$hasBoxImage ? 'no-product-image' : '' }}"
                                                             style="width: 100%; height: 100%; object-fit: cover;" loading="lazy" decoding="async">
                                                        <div class="checkbox-overlay" style="position: absolute; top: 10px; right: 10px; z-index: 10;">
                                                            @if($maxCount == 1 && $minCount <= 1)
                                                                <input type="radio"
                                                                       name="box_products[{{ $titleIndex }}][]"
                                                                       value="{{ $boxProduct->id }}"
                                                                       data-price="{{ $boxProduct->getCurrentPrice(session('currency')) }}"
                                                                       class="box-product-radio"
                                                                       data-title-index="{{ $titleIndex }}"
                                                                       data-min-count="{{ $minCount }}"
                                                                       data-max-count="{{ $maxCount }}"
                                                                       {{ $isRequired ? 'required' : '' }}>
                                                                <span class="custom-radio"></span>
                                                            @else
                                                                <input type="checkbox"
                                                                       name="box_products[{{ $titleIndex }}][]"
                                                                       value="{{ $boxProduct->id }}"
                                                                       data-price="{{ $boxProduct->getCurrentPrice(session('currency')) }}"
                                                                       class="box-product-checkbox"
                                                                       data-title-index="{{ $titleIndex }}"
                                                                       data-min-count="{{ $minCount }}"
                                                                       data-max-count="{{ $maxCount }}">
                                                                <span class="custom-checkbox"></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </label>
                                                <h5 style="font-size: 1rem; font-weight: 600; color: #333; margin-bottom: 10px; text-align: center; min-height: 40px; display: flex; align-items: center; justify-content: center;">
                                                    {{ $boxProduct->name }}
                                                </h5>
                                                @if($minCount > 0 || $maxCount > 1)
                                                    <div class="selection-info" style="font-size: 0.85rem; color: #666; text-align: center; margin-top: 5px; margin-bottom: 5px;">
                                                        @if($minCount > 0 && $maxCount > 1)
                                                            <span class="badge bg-info" style="font-size: 0.75rem;">{{ __('website.min') }}: {{ $minCount }} | {{ __('website.max') }}: {{ $maxCount }}</span>
                                                        @elseif($minCount > 0)
                                                            <span class="badge bg-info" style="font-size: 0.75rem;">{{ __('website.min') }}: {{ $minCount }}</span>
                                                        @elseif($maxCount > 1)
                                                            <span class="badge bg-info" style="font-size: 0.75rem;">{{ __('website.max') }}: {{ $maxCount }}</span>
                                                        @endif
                                                    </div>
                                                @endif

                                                <!-- Product Addons -->
                                                @php
                                                    $subgroupedAddons = $boxProduct->boxAddons->groupBy(fn($addon) => $addon->group->name ?? 'Other');
                                                @endphp
                                                @if($subgroupedAddons->count() > 0)
                                                    <div class="product-addons mt-3" style="border-top: 1px solid #e9ecef; padding-top: 10px;">
                                                        <small style="color: #666; display: block; margin-bottom: 5px;">{{ __('website.addons') }}:</small>
                                                        @foreach($subgroupedAddons as $addonGroup => $addons)
                                                            <div style="margin-bottom: 8px;">
                                                                <strong style="font-size: 0.8rem; color: #333;">{{ $addonGroup }}</strong>
                                                                <div style="margin-top: 5px;">
                                                                    @foreach($addons as $addon)
                                                                        <label style="display: block; font-size: 0.75rem; margin-bottom: 3px; cursor: pointer;">
                                                                            <input type="checkbox"
                                                                                   value="{{ $addon->id }}"
                                                                                   data-price="{{ $addon->getCurrentPrice(session('currency')) }}"
                                                                                   name="box_addons[{{ $boxProduct->id }}][]"
                                                                                   class="box-addon-checkbox"
                                                                                   style="margin-right: 3px;">
                                                                            <span>{{ $addon->name }} (+{{ $addon->getCurrentPrice(session('currency')) }} {{ \App\Models\Currency::getCurrentCurrencySign() }})</span>
                                                                        </label>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            <!-- Buy Now Button -->
                            <div class="buttons_wrapper w-100 spinner_wrap mt-5" style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <a href="#" id="buyNowBtn"
                                   data-product-id="{{ $product->id }}"
                                   data-is-box="1"
                                   class="main_bttn hvr-sweep-to-right">
                                    {{ __('website.buy_now') }} <small id="finalPrice">@php $priceDetails = $product->getPriceDetails(session('currency')); @endphp{{ number_format($priceDetails['discounted'], 2) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</small>
                                </a>
                                <div class="number__spinner">
                                    <span class="ns-btn">
                                        <a data-dir="up" class="quantity-btn" style=""><i class="fa fa-plus"></i></a>
                                    </span>
                                    <input type="text" id="quantity" class="pl-ns-value" value="1" maxlength=2 style="">
                                    <span class="ns-btn">
                                        <a data-dir="dwn" class="quantity-btn" style=""><i class="fa fa-minus"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="row mealDet_row">
                            <div class="col-12 orderMD_two">
                                <div class="between_flex mb-3">
                                    <div class="title_wrapper">
                                        <h3> {{ $product->name }} </h3>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span
                                            class="meal_price">
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
                                        </span>
                                        @auth('web')
                                            @if(auth('web')->user()->hasRole('admin'))
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPriceModal" data-product-id="{{ $product->id }}" style="padding: 5px 10px;">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                            @endif
                                        @endauth
                                    </div>
                                </div>
                                <div class="rate_flex">
                                    <div class="rate_span">
                                        <span> {{ __('website.rating') }} </span>
                                        <strong> 4.8 </strong>
                                        <i class="fa-solid fa-star"></i>
                                    </div>
                                    <div class="rate_span">
                                        <span> 44 {{ __('website.reviews') }} </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-7 orderMD_one">
                                <div class="mealW_thumb" style="position: relative;">
                                    @auth('web')
                                        <button
                                            class="absAdd_fav{{ $favoriteProductIds->contains($product->id) ? ' favorited' : '' }}"
                                            data-product-id="{{ $product->id }}"
                                            style="position: absolute; top: 15px; right: 15px; border: none; padding: 10px; border-radius: 50%; cursor: pointer; z-index: 10; box-shadow: 0 2px 8px rgba(0,0,0,0.1); background: {{ $favoriteProductIds->contains($product->id) ? '#ff4444' : 'white' }};">
                                            <i class="las la-heart"></i>

                                        </button>
                                    @endauth
                                    @php
                                        $productImage = $product->getFirstMediaUrl('products');
                                        $hasImage = !empty($productImage);
                                    @endphp
                                    <img src="{{ $productImage ?: $logoUrl }}" alt="" class="mealW_thimg {{ !$hasImage ? 'no-product-image' : '' }}" loading="eager" fetchpriority="high" decoding="async">
                                </div>
                                @if($enableProductNotes && auth('web')->check())
                                    <div class="mt-3">
                                        <a href="javascript:void(0)" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#productNotesModal" data-product-id="{{ $product->id }}" style="color: #f6d814; font-weight: 500;">
                                            <i class="fa fa-sticky-note me-2"></i> {{ __('website.add_note') }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="col-12 col-lg-5 orderMD_three">
                                <div class="nutrition_cardN">
                                    <h3 class="insideMD_title"> {{ __('website.Nutrition information') }} </h3>
                                    <div class="nutrInfo_table">
                                        @if($product->calories)
                                            <div class="nutrInfo_row">
                                                <span> {{ __('website.calories') ?? 'Calories' }} </span>
                                                <span class="nutrInfo_desc">{{ $product->calories }}kcal  </span>
                                            </div>
                                        @endif
                                        @foreach($product->definitions as $definition)
                                            <div class="nutrInfo_row">
                                                <span> {{ $definition->name }} </span>
                                                <span
                                                    class="nutrInfo_desc">{{ rtrim(rtrim(number_format((float)$definition->pivot->value, 2, '.', ''), '0'), '.') }}{{ $definition->unit }}  </span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="nutrit_ftbttom d__mob__none"> {!! $product->ingrediant_text !!}  </div>
                                </div>
                            </div>
                        </div>
                        <div class="text_cont textMeal_cont">
                            <p> {{ $product->description }} </p>
                        </div>
                        @if(count($groupedAddons) > 0)
                            <div class="accordion mbttom_65" id="accordionExample">
                                @foreach($groupedAddons as $addonGroupName => $groupData)
                                    @php
                                        $addons = $groupData['addons'] ?? collect();
                                        $group = $groupData['group'] ?? null;
                                        $groupSlug = \Illuminate\Support\Str::slug($addonGroupName);
                                        $isMandatory = $group && ($group['is_selection_mandatory'] ?? false);
                                        $maxSelections = $group ? ($group['max_selections'] ?? null) : null;
                                        $minSelections = $group && $isMandatory ? ($group['min_selections'] ?? null) : null;
                                    @endphp
                                    <div class="accordion-item addon-group-item"
                                         data-group-slug="{{ $groupSlug }}"
                                         data-is-mandatory="{{ $isMandatory ? '1' : '0' }}"
                                         data-max-selections="{{ $maxSelections ?? '' }}"
                                         data-min-selections="{{ $minSelections ?? '' }}">
                                        <h2 class="accordion-header">
                                            <div class="accordion-button" style="cursor: default;">
                                                {{ $addonGroupName }}
                                                <small>
                                                    @if($isMandatory && $minSelections && $maxSelections && $minSelections == $maxSelections)
                                                        ({{ __('website.select_exactly') }} {{ $minSelections }} {{ $minSelections == 1 ? __('website.item') : __('website.items') }})
                                                    @elseif($isMandatory && $minSelections && $maxSelections)
                                                        ({{ __('website.select_minimum') }} {{ $minSelections }} {{ __('website.and_maximum') }} {{ $maxSelections }} {{ __('website.items') }})
                                                    @elseif($maxSelections)
                                                        ({{ __('website.choose_up_to') }} {{ $maxSelections }} {{ __('website.items') }})
                                                    @elseif($isMandatory && $minSelections)
                                                        ({{ __('website.select_minimum') }} {{ $minSelections }} {{ __('website.items') }})
                                                    @endif
                                                </small>
                                            </div>
                                        </h2>
                                        <div id="collapseOne-{{ $groupSlug }}" class="accordion-collapse show">
                                            <div class="accordion-body">
                                                <div class="checkACC_flex">
                                                    @php
                                                        $addonsCount = $addons->count();
                                                        $maxSelectionsInt = is_numeric($maxSelections) ? (int)$maxSelections : null;
                                                        $minSelectionsInt = is_numeric($minSelections) ? (int)$minSelections : null;
                                                        $isExactOneRequired = $isMandatory && $minSelectionsInt === 1 && $maxSelectionsInt === 1;
                                                        $isSingleRequired = $isMandatory && (
                                                            ($addonsCount === 1) ||
                                                            ($maxSelectionsInt === 1 && $addonsCount > 0)
                                                        ) && !$isExactOneRequired;
                                                    @endphp
                                                    @foreach($addons as $addon)
                                                        <label class="checkACC_label">
                                                            @if($isSingleRequired)
                                                                <input value="{{ $addon->id }}"
                                                                       data-price="{{ $addon->getCurrentPrice(session('currency')) }}"
                                                                       name="addons[{{ $groupSlug }}]"
                                                                       type="radio"
                                                                       class="checkACC_box addon-radio"
                                                                       data-group-slug="{{ $groupSlug }}"
                                                                       checked>
                                                            @else
                                                                <input value="{{ $addon->id }}"
                                                                       data-price="{{ $addon->getCurrentPrice(session('currency')) }}"
                                                                       name="addons[]"
                                                                       type="checkbox"
                                                                       class="checkACC_box addon-checkbox"
                                                                       data-group-slug="{{ $groupSlug }}">
                                                            @endif
                                                            <span class="checkACC_text">
                                                                {{ $addon->name }} ({{ $addon->getCurrentPrice(session('currency')) }} {{ \App\Models\Currency::getCurrentCurrencySign() }})
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                        @endif
                        @if(!isset($fromMenu) || !$fromMenu)
                            <div class="buttons_wrapper w-100 spinner_wrap">
                                <a href="#" id="addToCartBtn"
                                   data-product-id="{{ $product->id }}"
                                   data-is-box="0"
                                   class="main_bttn hvr-sweep-to-right">
                                    + {{ __('website.add_cart') }} <small
                                        id="finalPrice">@php $priceDetails = $product->getPriceDetails(session('currency')); @endphp{{ number_format($priceDetails['discounted'], 2) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</small>
                                </a>
                                <div class="number__spinner">
        <span class="ns-btn">
            <a data-dir="up" class="quantity-btn"><i class="fa fa-plus"></i></a>
        </span>
                                    <input type="text" id="quantity" class="pl-ns-value" value="1" maxlength=2>
                                    <span class="ns-btn">
            <a data-dir="dwn" class="quantity-btn"><i class="fa fa-minus"></i></a>
        </span>
                                </div>
                            </div>
                        @endif

                    @endif
                </div>
            </div>
        </div>
    </section>

    <!--start plates section-->
    @if((!isset($fromMenu) || !$fromMenu) && count($related_products) > 0)
        <section class="pickup_section wide_padding">
            <div class="container px-lg-0">
                <div class="between_flex mb-5">
                    <div class="title_wrapper">
                        <h3> {{ __('website.Similar plates') }} </h3>
                    </div>
                </div>
                <div class="related_products_slider">
                    @foreach($related_products as $related_product)
                        <div class="productOne_itemN">
                            <div class="product_cardN1">
                                @auth('web')
                                    <button
                                        class="absAdd_fav d__mob__none{{ $favoriteProductIds->contains($related_product->id) ? ' favorited' : '' }}"
                                        data-product-id="{{ $related_product->id }}"
                                        style="{{ $favoriteProductIds->contains($related_product->id) ? 'background-color: #ff4444;' : '' }}">
                                        <i class="las la-heart"></i>
                                    </button>
                                @endauth
                                <a href="{{ route('website.products',$related_product->slug) }}" class="prodThumb_link">
                                    @php
                                        $relatedImage = $related_product->getFirstMediaUrl('products');
                                        $hasRelatedImage = !empty($relatedImage);
                                    @endphp
                                    <img src="{{ $relatedImage ?: $logoUrl }}" alt=""
                                         class="prodThumb_img {{ !$hasRelatedImage ? 'no-product-image' : '' }}" loading="lazy" decoding="async">
                                </a>
                                <div class="content_box">
                                    <h5 class="pro_title"><a
                                            href="{{ route('website.products',$related_product->slug) }}"> {{ $related_product->name }} </a>
                                    </h5>
                                    <div class="content_bInfo">
                                        <span class="health_status"> {{ __('website.healthy') }} </span>
                                        <div
                                            class="pro_price">
                                            @php
                                                $priceDetails = $related_product->getPriceDetails(session('currency'));
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
                                        @if($related_product->calories)
                                            <div class="kcal_flex">
                                                <i class="kcal_icon"></i>
                                                <span>{{ $related_product->calories }}kcal</span>
                                            </div>
                                        @endif
                                        <div class="fats_info">
                                            @foreach($related_product->definitions as $definition)
                                                <div class="carbIN_flex">
                                                    <span> {{ $definition->name }} </span>
                                                    <strong>{{ rtrim(rtrim(number_format((float)$definition->pivot->value, 2, '.', ''), '0'), '.') }}{{ $definition->unit }}</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @if(!isset($fromMenu) || !$fromMenu)
                                        @if(isset($cartProductIds[$related_product->id]))
                                            @php
                                                $hasAddons = $related_product->addonGroups()->count() > 0 || $related_product->addons()->count() > 0;
                                            @endphp
                                            <div class="number__spinner wide_spinner w-100 product-spinner-{{ $related_product->id }}"
                                                 data-product-id="{{ $related_product->id }}"
                                                 data-product-slug="{{ $related_product->slug }}"
                                                 data-has-addons="{{ $hasAddons ? '1' : '0' }}">
                                                <span class="ns-btn">
                                                    <a data-dir="up">
                                                        <i class="fa fa-plus"></i>
                                                    </a>
                                                </span>
                                                <input type="text" class="pl-ns-value" value="{{ $cartQuantities[$related_product->id] ?? 1 }}" maxlength="2" readonly>
                                                <span class="ns-btn">
                                                    <a data-dir="dwn" class="remove-product-btn">
                                                        <i class="icon_trash"></i>
                                                    </a>
                                                </span>
                                            </div>
                                        @else
                                            <div class="buttons_wrapper w-100 product-buttons-{{ $related_product->id }}">
                                                @php
                                                    $hasAddons = $related_product->addonGroups()->count() > 0 || $related_product->addons()->count() > 0;
                                                @endphp
                                                @if($hasAddons)
                                                    <a href="{{ route('website.products', $related_product->slug) }}"
                                                       class="main_bttn hvr-sweep-to-right">{{ __('website.add_to_cart') }}</a>
                                                @else
                                                    <a href="javascript:void(0)"
                                                       class="main_bttn hvr-sweep-to-right quick-add-to-cart"
                                                       data-product-id="{{ $related_product->id }}"
                                                       data-has-addons="0">{{ __('website.add_to_cart') }}</a>
                                                @endif
                                                <a href="{{ route('website.products', $related_product->slug) }}"
                                                   class="main_bttn white_bttn hvr-sweep-to-right"> {{ __('website.buy_now') }} </a>
                                            </div>
                                        @endif
                                    @else
                                        <div class="buttons_wrapper w-100 ">
                                            <a href="{{ route('website.products',$related_product->slug) }}"
                                               class="main_bttn white_bttn hvr-sweep-to-right">
                                                {{ __('website.view_product') }}</a>
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
    <!--start Bold download section-->
    <!--/ Content -->

    <!-- Edit Price Modal -->
    @auth('web')
        @if(auth('web')->user()->hasRole('admin'))
            <div class="modal fade" id="editPriceModal" tabindex="-1" aria-labelledby="editPriceModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editPriceModalLabel">{{ __('admin.edit_price') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editPriceForm">
                                @csrf
                                <input type="hidden" name="product_id" id="modal_product_id">
                                <div id="pricesContainer"></div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                            <button type="button" class="btn btn-primary" id="savePriceBtn">{{ __('admin.save') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endauth
@endsection
@section('website-footer')
    <script>


        document.addEventListener('DOMContentLoaded', function () {
            const basePrice = parseFloat("{{ $product->getCurrentPrice(session('currency')) }}");
            const currencySign = "{{ \App\Models\Currency::getCurrentCurrencySign() }}";

            const checkboxes = document.querySelectorAll('.checkACC_box');
            const quantityInput = document.getElementById('quantity');
            const finalPriceElement = document.getElementById('finalPrice');

            function calculateTotal() {
                let total = basePrice;
                document.querySelectorAll('.checkACC_box:checked').forEach(checkbox => {
                    const price = parseFloat(checkbox.dataset.price || '0');
                    total += isNaN(price) ? 0 : price;
                });
                const quantity = parseInt(quantityInput?.value) || 1;
                total = total * quantity;
                if (finalPriceElement) {
                    finalPriceElement.textContent = `${total.toFixed(3)} ${currencySign}`;
                }
            }

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', calculateTotal);
            });
            // Also listen to radio buttons
            document.querySelectorAll('.addon-radio').forEach(radio => {
                radio.addEventListener('change', calculateTotal);
            });
            document.querySelectorAll('.quantity-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const dir = this.getAttribute('data-dir');
                    let quantity = parseInt(quantityInput?.value) || 1;

                    if (dir === 'up') quantity++;
                    if (dir === 'dwn' && quantity > 1) quantity--;

                    if (quantityInput) quantityInput.value = quantity;
                    calculateTotal();
                });
            });
            if (quantityInput) quantityInput.addEventListener('input', calculateTotal);
            calculateTotal();
        });
    </script>
    @auth('web')
        @if(auth('web')->user()->hasRole('admin'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const editPriceModal = document.getElementById('editPriceModal');
                    const savePriceBtn = document.getElementById('savePriceBtn');
                    const editPriceForm = document.getElementById('editPriceForm');

                    if (editPriceModal) {
                        editPriceModal.addEventListener('show.bs.modal', function (event) {
                            const button = event.relatedTarget;
                            const productId = button.getAttribute('data-product-id');
                            document.getElementById('modal_product_id').value = productId;

                            // Fetch product prices
                            fetch(`/{{ app()->getLocale() }}/products/${productId}/prices`)
                                .then(response => response.json())
                                .then(data => {
                                    const container = document.getElementById('pricesContainer');
                                    container.innerHTML = '';

                                    if (data.prices && data.prices.length > 0) {
                                        data.prices.forEach(price => {
                                            const priceRow = document.createElement('div');
                                            priceRow.className = 'row mb-3';
                                            priceRow.innerHTML = `
                                                <div class="col-md-4">
                                                    <label class="form-label">${price.currency_name}</label>
                                                    <input type="hidden" name="prices[${price.currency_id}][currency_id]" value="${price.currency_id}">
                                                    <select class="form-control discount-type-select" name="prices[${price.currency_id}][discount_type]" data-currency="${price.currency_id}">
                                                        <option value="none" ${price.discount_type === 'none' || !price.discount_type ? 'selected' : ''}>${data.translations.no_discount}</option>
                                                        <option value="percentage" ${price.discount_type === 'percentage' ? 'selected' : ''}>${data.translations.percent}</option>
                                                        <option value="fixed" ${price.discount_type === 'fixed' ? 'selected' : ''}>${data.translations.fixed}</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">${data.translations.price} <small class="text-danger">${data.translations.before_discount}</small></label>
                                                    <div class="input-group">
                                                        <input type="number" step="0.01" min="0" class="form-control price-before" name="prices[${price.currency_id}][before]" value="${price.price || ''}" required>
                                                        <span class="input-group-text">${price.currency_sign}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 discount-field" style="display: ${price.discount_type && price.discount_type !== 'none' ? 'block' : 'none'};">
                                                    <label class="form-label">${data.translations.price} <small class="text-success">${data.translations.after_discount}</small></label>
                                                    <div class="input-group">
                                                        <input type="number" step="0.01" min="0" class="form-control price-after" name="prices[${price.currency_id}][after]" value="${price.discount_price || ''}">
                                                        <span class="input-group-text">${price.currency_sign}</span>
                                                    </div>
                                                </div>
                                            `;
                                            container.appendChild(priceRow);
                                        });

                                        // Handle discount type change
                                        container.querySelectorAll('.discount-type-select').forEach(select => {
                                            select.addEventListener('change', function() {
                                                const currencyId = this.getAttribute('data-currency');
                                                const discountField = this.closest('.row').querySelector('.discount-field');
                                                if (this.value === 'none') {
                                                    discountField.style.display = 'none';
                                                    this.closest('.row').querySelector('.price-after').value = '';
                                                } else {
                                                    discountField.style.display = 'block';
                                                }
                                            });
                                        });
                                    } else {
                                        container.innerHTML = '<p class="text-center">' + data.translations.no_prices + '</p>';
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    AppSwal.error('Failed to load prices', '{{ __("website.an_error_occurred") }}');
                                });
                        });

                        if (savePriceBtn) {
                            savePriceBtn.addEventListener('click', function() {
                                const formData = new FormData(editPriceForm);
                                const productId = document.getElementById('modal_product_id').value;

                                fetch(`/{{ app()->getLocale() }}/products/${productId}/update-price`, {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        AppSwal.success('{{ __("admin.prices_updated_successfully") ?? "Prices updated successfully" }}', '{{ __("admin.update_success") }}').then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        AppSwal.error(data.message || 'Failed to update prices', '{{ __("admin.Error") }}');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    AppSwal.error('Failed to update prices', '{{ __("admin.Error") }}');
                                });
                            });
                        }
                    }
                });
            </script>
        @endif
    @endauth
    <script>
        // Favorite button functionality - using event delegation
        $(document).ready(function () {
            $(document).on('click', '.absAdd_fav', function (e) {
                e.preventDefault();
                e.stopPropagation();

                @auth('web')
                var button = $(this);
                var productId = button.attr('data-product-id') || button.data('product-id');
                var isFavorited = button.hasClass('favorited');

                console.log('Button element:', button);
                console.log('Product ID:', productId); // Debug
                console.log('Is Favorited:', isFavorited); // Debug

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
                Swal.fire({
                    icon: 'warning',
                    title: '<div style="font-size: 28px; font-weight: 600; color: #ffc107; margin-bottom: 10px;">⚠ {{ __("website.login_required") }}</div>',
                    html: '<div style="font-size: 16px; color: #333; padding: 10px 0;">{{ __("website.please_login_to_add_favorites") }}</div>',
                    showCancelButton: true,
                    confirmButtonText: '{{ __("website.login") }}',
                    cancelButtonText: '{{ __("website.cancel") }}',
                    customClass: {
                        popup: 'swal2-popup-custom-warning',
                        icon: 'swal2-icon-custom-warning',
                        title: 'swal2-title-custom'
                    },
                    showClass: {
                        popup: 'swal2-show-custom',
                        icon: 'swal2-icon-show-custom'
                    },
                    hideClass: {
                        popup: 'swal2-hide-custom'
                    },
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route("website.login") }}';
                    }
                });
                @endauth
            });
        });
    </script>
    @if(!isset($fromMenu) || !$fromMenu)
        <script>
            $(document).ready(function () {
                // Make image clickable to select product
                $('.product-image-wrapper').on('click', function(e) {
                    e.preventDefault();
                    const $wrapper = $(this);
                    const $input = $wrapper.find('input[type="radio"], input[type="checkbox"]');

                    if ($input.length) {
                        if ($input.attr('type') === 'radio') {
                            $input.prop('checked', true).trigger('change');
                        } else {
                            $input.prop('checked', !$input.prop('checked')).trigger('change');
                        }
                    }
                });

                // Update card selected state
                function updateCardState() {
                    $('.box-product-card').each(function() {
                        const $card = $(this);
                        const $input = $card.find('input[type="radio"]:checked, input[type="checkbox"]:checked');
                        if ($input.length > 0) {
                            $card.addClass('selected');
                        } else {
                            $card.removeClass('selected');
                        }
                    });
                }

                // Handle checkbox max count validation
                $('.box-product-checkbox').on('change', function() {
                    updateCardState();
                    let maxCount = parseInt($(this).data('max-count')) || 1;
                    let minCount = parseInt($(this).data('min-count')) || 0;
                    let titleIndex = $(this).data('title-index');
                    let checkedCount = $(`.box-product-checkbox[data-title-index="${titleIndex}"]:checked`).length;

                    if (checkedCount > maxCount) {
                        $(this).prop('checked', false);
                        updateCardState();
                        Swal.fire({
                            icon: 'warning',
                            title: '{{ __("website.max_selection_reached") }}',
                            text: '{{ __("website.you_can_only_select") }} ' + maxCount + ' {{ __("website.items") }}',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }

                    // Show warning if minimum not met (but don't prevent unchecking)
                    let $card = $(this).closest('.title-group-card');
                    $card.find('.min-count-warning').remove();
                    if (minCount > 0 && checkedCount < minCount) {
                        $card.find('.title-header').append('<span class="min-count-warning text-danger ms-2" style="font-size: 0.85rem;">{{ __("website.minimum_selection_required") }}: ' + minCount + '</span>');
                    }
                });

                $('.addon-checkbox').on('change', function() {
                    let $checkbox = $(this);
                    let groupSlug = $checkbox.data('group-slug');
                    let $groupItem = $(`.addon-group-item[data-group-slug="${groupSlug}"]`);
                    let $radioButtons = $(`.addon-radio[data-group-slug="${groupSlug}"]`);
                    if ($radioButtons.length > 0) {
                        return;
                    }

                    let isMandatory = $groupItem.data('is-mandatory') == '1';
                    let maxSelections = $groupItem.data('max-selections');
                    let minSelections = $groupItem.data('min-selections');

                    if (maxSelections === '') maxSelections = null;
                    if (minSelections === '') minSelections = null;

                    if (maxSelections !== null && $checkbox.is(':checked')) {
                        const maxSelectionsInt = parseInt(maxSelections);
                        let $checkedItems = $(`.addon-checkbox[data-group-slug="${groupSlug}"]:checked`);
                        if (!isNaN(maxSelectionsInt) && $checkedItems.length > maxSelectionsInt) {
                            const itemsToRemoveCount = $checkedItems.length - maxSelectionsInt;
                            $checkedItems.not($checkbox).slice(0, itemsToRemoveCount).prop('checked', false);
                        }
                    }

                    let checkedCount = $(`.addon-checkbox[data-group-slug="${groupSlug}"]:checked`).length;

                    // Show warning if minimum not met (but don't prevent unchecking)
                    $groupItem.find('.min-selections-warning').remove();
                    if (isMandatory && minSelections !== null && checkedCount < parseInt(minSelections)) {
                        $groupItem.find('.accordion-header').append('<span class="min-selections-warning text-danger ms-2" style="font-size: 0.85rem;">{{ __("website.minimum_selection_required") }}: ' + minSelections + '</span>');
                    } else {
                        $groupItem.find('.min-selections-warning').remove();
                    }
                });

                // Handle radio button change
                $('.box-product-radio').on('change', function() {
                    updateCardState();
                });

                // Calculate total price including box products
                function calculateBoxTotal() {
                    let total = parseFloat("{{ $product->getCurrentPrice(session('currency')) }}");

                    // Add main addons (both checkbox and radio)
                    $('input[name="addons[]"].checkACC_box:checked, .addon-radio:checked').each(function () {
                        total += parseFloat($(this).data('price') || 0);
                    });

                    // Note: Box product prices are NOT added - the box has a fixed price
                    // Only the addons for selected box products are added

                    // Add box product addons only (not the product prices themselves)
                    $('.box-addon-checkbox:checked').each(function () {
                        total += parseFloat($(this).data('price') || 0);
                    });

                    // Multiply by quantity
                    let quantity = parseInt($('#quantity').val()) || 1;
                    total = total * quantity;

                    $('#finalPrice').text(total.toFixed(3) + ' {{ \App\Models\Currency::getCurrentCurrencySign() }}');
                }

                // Update price on any change
                $('.checkACC_box, .box-product-radio, .box-product-checkbox, .box-addon-checkbox, #quantity').on('change', calculateBoxTotal);
                $('#quantity').on('input', calculateBoxTotal);

                // Buy Now button for boxes - adds to cart and redirects to checkout
                $('#buyNowBtn').on('click', function (e) {
                    e.preventDefault();

                    // Validate city selection
                    if (typeof validateCitySelection === 'function' && !validateCitySelection()) {
                        return;
                    }

                    let productId = $(this).data('product-id');
                    let isBox = $(this).data('is-box') == 1;
                    let quantity = $('#quantity').val() || 1;
                    let addons = [];
                    let boxAddons = {};
                    let subproducts = [];

                    // Store original button HTML for restoration
                    const $button = $(this);
                    const originalButtonHtml = $button.html();

                    if (isBox) {
                        // Collect main product addons
                        // Collect addons from both checkboxes and radio buttons
                        $('input[name="addons[]"].checkACC_box:checked, .addon-radio:checked').each(function () {
                            addons.push($(this).val());
                        });

                        // Collect box products and their addons
                        let selectedProductIds = [];
                        $('.box-product-radio:checked, .box-product-checkbox:checked').each(function () {
                            let productId = parseInt($(this).val());
                            if (productId && !selectedProductIds.includes(productId)) {
                                selectedProductIds.push(productId);

                                let productAddons = [];
                                $("input[name='box_addons[" + productId + "][]']:checked").each(function () {
                                    productAddons.push(parseInt($(this).val()));
                                });

                                subproducts.push({
                                    product_id: productId,
                                    addons: productAddons
                                });
                            }
                        });

                        // Collect box_addons in legacy format
                        $("input[name^='box_addons']").filter(':checked').each(function () {
                            let name = $(this).attr('name');
                            let match = name.match(/box_addons\[(\d+)\]/);
                            if (match) {
                                let pid = match[1];
                                if (!boxAddons[pid]) boxAddons[pid] = [];
                                boxAddons[pid].push($(this).val());
                            }
                        });

                        // Validate that required products are selected
                        let hasRequiredError = false;
                        $('.title-group-card.required').each(function() {
                            let hasSelection = $(this).find('.box-product-radio:checked, .box-product-checkbox:checked').length > 0;
                            if (!hasSelection) {
                                hasRequiredError = true;
                                return false;
                            }
                        });

                        if (hasRequiredError) {
                            AppSwal.warning('{{ __("website.please_select_required_products") }}', '{{ __("website.error") }}');
                            return;
                        }

                        if (subproducts.length === 0) {
                            AppSwal.warning('{{ __("website.please_select_at_least_one_product") }}', '{{ __("website.error") }}');
                            return;
                        }
                    } else {
                        // Collect addons for regular products
                        // Collect addons from checkboxes and radio buttons
                        $('input[name="addons[]"].checkACC_box:checked, .addon-checkbox:checked, .addon-radio:checked').each(function () {
                            addons.push($(this).val());
                        });
                    }

                    // Validate mandatory addon selections
                    let hasAddonError = false;
                    let addonErrorMessage = '';
                    $('.addon-group-item').each(function() {
                        let $groupItem = $(this);
                        let isMandatory = $groupItem.data('is-mandatory') == '1';
                        let minSelections = $groupItem.data('min-selections');
                        let groupSlug = $groupItem.data('group-slug');
                        let groupName = $groupItem.find('.accordion-button').text().trim();

                        if (minSelections === '') minSelections = null;

                        if (isMandatory && minSelections !== null) {
                            // Count both checkboxes and radio buttons
                            let checkedCount = $(`.addon-checkbox[data-group-slug="${groupSlug}"]:checked, .addon-radio[data-group-slug="${groupSlug}"]:checked`).length;
                            if (checkedCount < parseInt(minSelections)) {
                                hasAddonError = true;
                                addonErrorMessage = '{{ __("website.please_select_minimum_addons") }}: ' + groupName + ' ({{ __("website.minimum") }}: ' + minSelections + ')';
                                return false;
                            }
                        }
                    });

                    if (hasAddonError) {
                        AppSwal.warning(addonErrorMessage, '{{ __("website.error") }}');
                        return;
                    }

                    $.ajax({
                        url: "{{ route('cart.add') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            product_id: productId,
                            quantity: quantity,
                            addons: addons,
                            is_box: isBox ? 1 : 0,
                            subproducts: isBox ? subproducts : [],
                            box_addons: boxAddons
                        },
                        beforeSend: function () {
                            $button.addClass('disabled').text('{{ __("website.processing") }}...');
                        },
                        success: function (response) {
                            if (response.status !== false) {
                                // Redirect to checkout
                                window.location.href = "{{ route('checkout') }}";
                            } else {
                                AppSwal.error(response.message || '{{ __("website.something_went_wrong") }}', '{{ __("website.error") }}');
                                $button.removeClass('disabled').html(originalButtonHtml);
                            }
                        },
                        error: function (xhr) {
                            let errorMessage = '{{ __("website.something_went_wrong") }}';

                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }

                            AppSwal.error(errorMessage, '{{ __("website.error") }}');

                            $button.removeClass('disabled').html(originalButtonHtml);
                        }
                    });
                });

                $('#addToCartBtn').on('click', function (e) {
                    e.preventDefault();

                    // Validate city selection
                    if (typeof validateCitySelection === 'function' && !validateCitySelection()) {
                        return;
                    }

                    let productId = $(this).data('product-id');
                    let isBox = $(this).data('is-box') == 1;
                    let quantity = $('#quantity').val() || 1;
                    let addons = [];
                    let boxAddons = {};
                    let subproducts = []; // Declare outside if block to avoid scope issues

                    // Store original button HTML for restoration
                    const $button = $(this);
                    const originalButtonHtml = $button.html();

                    if (isBox) {
                        // Collect main product addons
                        // Collect addons from both checkboxes and radio buttons
                        $('input[name="addons[]"].checkACC_box:checked, .addon-radio:checked').each(function () {
                            addons.push($(this).val());
                        });

                        // Collect box products and their addons in the format expected by backend
                        // Backend expects: subproducts: [{product_id: X, addons: []}]
                        let selectedProductIds = [];

                        // First, collect all selected products
                        $('.box-product-radio:checked, .box-product-checkbox:checked').each(function () {
                            let productId = parseInt($(this).val());
                            if (productId && !selectedProductIds.includes(productId)) {
                                selectedProductIds.push(productId);

                                // Collect addons for this specific product
                                let productAddons = [];
                                $("input[name='box_addons[" + productId + "][]']:checked").each(function () {
                                    productAddons.push(parseInt($(this).val()));
                                });

                                subproducts.push({
                                    product_id: productId,
                                    addons: productAddons
                                });
                            }
                        });

                        // Also collect box_addons in legacy format for backward compatibility
                        $("input[name^='box_addons']").filter(':checked').each(function () {
                            let name = $(this).attr('name');
                            let match = name.match(/box_addons\[(\d+)\]/);
                            if (match) {
                                let pid = match[1];
                                if (!boxAddons[pid]) boxAddons[pid] = [];
                                boxAddons[pid].push($(this).val());
                            }
                        });

                        // Validate that required products are selected and minimum counts are met
                        let hasRequiredError = false;
                        let validationMessage = '';

                        $('.title-group-card').each(function() {
                            let $card = $(this);
                            let titleIndex = $card.data('title-index') || $card.index();

                            // Get min_count and max_count from first checkbox/radio in this group
                            let $firstInput = $card.find('.box-product-radio, .box-product-checkbox').first();
                            let minCount = parseInt($firstInput.data('min-count')) || 0;
                            let maxCount = parseInt($firstInput.data('max-count')) || 1;
                            let isRequired = $card.hasClass('required');

                            let selectedCount = $card.find('.box-product-radio:checked, .box-product-checkbox:checked').length;

                            // Check if required and no selection
                            if (isRequired && selectedCount === 0) {
                                hasRequiredError = true;
                                validationMessage = '{{ __("website.please_select_required_products") }}';
                                return false;
                            }

                            // Check minimum count
                            if (minCount > 0 && selectedCount < minCount) {
                                hasRequiredError = true;
                                validationMessage = '{{ __("website.please_select_minimum_products") }}'.replace(':min', minCount);
                                return false;
                            }

                            // Check maximum count
                            if (selectedCount > maxCount) {
                                hasRequiredError = true;
                                validationMessage = '{{ __("website.please_select_maximum_products") }}'.replace(':max', maxCount);
                                return false;
                            }
                        });

                        if (hasRequiredError) {
                            AppSwal.warning(validationMessage || '{{ __("website.please_select_required_products") }}', '{{ __("website.error") }}');
                            $button.removeClass('disabled').html(originalButtonHtml);
                            return;
                        }

                        if (subproducts.length === 0) {
                            AppSwal.warning('{{ __("website.please_select_at_least_one_product") }}', '{{ __("website.error") }}');
                            $button.removeClass('disabled').html(originalButtonHtml);
                            return;
                        }
                    } else {
                        // Collect addons for regular products
                        // Collect addons from checkboxes and radio buttons
                        $('input[name="addons[]"].checkACC_box:checked, .addon-checkbox:checked, .addon-radio:checked').each(function () {
                            addons.push($(this).val());
                        });
                    }

                    // Validate mandatory addon selections
                    let hasAddonError = false;
                    let addonErrorMessage = '';
                    $('.addon-group-item').each(function() {
                        let $groupItem = $(this);
                        let isMandatory = $groupItem.data('is-mandatory') == '1';
                        let minSelections = $groupItem.data('min-selections');
                        let groupSlug = $groupItem.data('group-slug');
                        let groupName = $groupItem.find('.accordion-button').text().trim();

                        if (minSelections === '') minSelections = null;

                        if (isMandatory && minSelections !== null) {
                            // Count both checkboxes and radio buttons
                            let checkedCount = $(`.addon-checkbox[data-group-slug="${groupSlug}"]:checked, .addon-radio[data-group-slug="${groupSlug}"]:checked`).length;
                            if (checkedCount < parseInt(minSelections)) {
                                hasAddonError = true;
                                addonErrorMessage = '{{ __("website.please_select_minimum_addons") }}: ' + groupName + ' ({{ __("website.minimum") }}: ' + minSelections + ')';
                                return false;
                            }
                        }
                    });

                    if (hasAddonError) {
                        AppSwal.warning(addonErrorMessage, '{{ __("website.error") }}');
                        $button.removeClass('disabled').html(originalButtonHtml);
                        return;
                    }

                    // Debug logging
                    console.log('Add to Cart Data:', {
                        product_id: productId,
                        quantity: quantity,
                        is_box: isBox,
                        addons: addons,
                        subproducts: isBox ? subproducts : [],
                        box_addons: boxAddons
                    });

                    $.ajax({
                        url: "{{ route('cart.add') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            product_id: productId,
                            quantity: quantity,
                            addons: addons,
                            is_box: isBox ? 1 : 0,
                            subproducts: isBox ? subproducts : [],
                            box_addons: boxAddons
                        },
                        beforeSend: function () {
                            $button.addClass('disabled').text('{{ __("website.adding") }}...');
                        },
                        success: function (response) {
                            console.log('Add to Cart Response:', response);

                            if (response.status !== false) {
                                $button.removeClass('disabled').html('+ Added <small>' + response.total + ' {{ \App\Models\Currency::getCurrentCurrencySign() }}</small>');

                                // Update cart count badges
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

                                AppSwal.success(response.message || '{{ __("website.product_added_successfully") }}', '{{ __("website.added_to_cart") }}');

                                // Update cart total in sidebar
                                if (response.cart_total && $('.total_box').length) {
                                    $('.total_box').html('<strong>Total:</strong> ' + response.cart_total + ' ' + (response.currency || '{{ \App\Models\Currency::getCurrentCurrencySign() }}'));
                                }

                                // Reload cart sidebar
                                if ($('#sideCart_menu .cartSide_content').length) {
                                    $('#sideCart_menu .cartSide_content').load(window.location.href + " #sideCart_menu .cartSide_content > *");
                                }
                            } else {
                                AppSwal.error(response.message || '{{ __("website.something_went_wrong") }}');
                                $button.removeClass('disabled').html(originalButtonHtml);
                            }
                        },
                        error: function (xhr) {
                            console.error('Add to Cart Error:', xhr);
                            let errorMessage = '{{ __("website.something_went_wrong") }}';

                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            } else if (xhr.status === 422) {
                                errorMessage = '{{ __("website.validation_error") }}';
                                if (xhr.responseJSON && xhr.responseJSON.errors) {
                                    let errors = Object.values(xhr.responseJSON.errors).flat();
                                    if (errors.length > 0) {
                                        errorMessage = errors[0];
                                    }
                                }
                            } else if (xhr.status === 404) {
                                errorMessage = '{{ __("website.product_not_found") }}';
                            } else if (xhr.status === 500) {
                                errorMessage = '{{ __("website.server_error") }}';
                            }

                            AppSwal.error(errorMessage);

                            $button.removeClass('disabled').html(originalButtonHtml);
                        }
                    });
                });
            });
        </script>
    @endif

    <script>
        // Quick add to cart for related products (products without addons)
        $(document).ready(function () {
            $(document).on('click', '.quick-add-to-cart', function(e) {
                e.preventDefault();

                // Validate city selection
                if (typeof validateCitySelection === 'function' && !validateCitySelection()) {
                    return;
                }

                const $button = $(this);
                const productId = $button.data('product-id');
                // Find the closest buttons wrapper to this specific button
                let $buttonsWrapper = $button.closest('.product-buttons-' + productId);

                // If not found, try finding by parent
                if ($buttonsWrapper.length === 0) {
                    $buttonsWrapper = $button.closest('.buttons_wrapper');
                }

                // Store original button HTML for restoration
                const originalButtonHtml = $button.html();

                // Disable button during request
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
                        console.log('Quick Add to Cart Response:', response);

                        if (response.status !== false) {
                            // Update cart count badges
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
                            const $productCard = $button.closest('.product_cardN1, .productOne_itemN');
                            const $productLink = $productCard.find('a[href*="/products/"]');
                            const productSlug = $productLink.attr('href') ? $productLink.attr('href').split('/products/')[1] : '';
                            const hasAddons = $button.data('has-addons') || '0';

                            // Replace buttons wrapper with number spinner
                            const spinnerHtml = `
                                <div class="number__spinner wide_spinner w-100 product-spinner-${productId}"
                                     data-product-id="${productId}"
                                     data-product-slug="${productSlug}"
                                     data-has-addons="${hasAddons}">
                                    <span class="ns-btn">
                                        <a data-dir="up">
                                            <i class="fa fa-plus"></i>
                                        </a>
                                    </span>
                                    <input type="text" class="pl-ns-value" value="1" maxlength="2" readonly>
                                    <span class="ns-btn">
                                        <a data-dir="dwn" class="remove-product-btn">
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

                            // Update cart total in sidebar
                            if (response.cart_total && $('.total_box').length) {
                                $('.total_box').html('<strong>Total:</strong> ' + response.cart_total + ' ' + (response.currency || '{{ \App\Models\Currency::getCurrentCurrencySign() }}'));
                            }

                            // Refresh cart sidebar
                            if ($('#sideCart_menu .cartSide_content').length) {
                                $('#sideCart_menu .cartSide_content').load(window.location.href + " #sideCart_menu .cartSide_content > *");
                            }

                            // Show success toast
                            AppSwal.success('{{ __("website.added_to_cart") }}');
                        } else {
                            AppSwal.error(response.message || '{{ __("website.something_went_wrong") }}');
                            $button.removeClass('disabled').html(originalButtonHtml);
                        }
                    },
                    error: function(xhr) {
                        console.error('Quick Add to Cart Error:', xhr);
                        let errorMessage = '{{ __("website.something_went_wrong") }}';

                        // Try to get error message from response
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.message) {
                                    errorMessage = response.message;
                                }
                            } catch (e) {
                                // If parsing fails, use default message
                            }
                        }

                        AppSwal.error(errorMessage, '{{ __("website.error") }}');
                        $button.removeClass('disabled').html(originalButtonHtml);
                    }
                });
            });

            // Handle number spinner for cart quantity update (for related products)
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

                // Skip if this spinner doesn't have product-id (e.g., quantity spinner on product page)
                // This spinner is handled by its own handler (quantity-btn)
                if (!productId) {
                    return; // Don't show error, just ignore - this is likely the quantity selector on product page
                }

                if (direction === 'up') {
                    newQty = currentQty + 1;
                } else if (direction === 'dwn') {
                    // If quantity is 1, remove from cart; otherwise decrement
                    if (currentQty === 1) {
                        // Disable buttons during removal
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
                                    // Get product info from spinner data attributes
                                    const productSlug = $spinner.data('product-slug') || '';
                                    const hasAddons = $spinner.data('has-addons') === '1' || $spinner.data('has-addons') === 1;

                                    // Replace spinner with buttons
                                    let buttonsHtml = '<div class="buttons_wrapper w-100 product-buttons-' + productId + '">';

                                    if (hasAddons) {
                                        buttonsHtml += '<a href="{{ url("/products") }}/' + productSlug + '" class="main_bttn hvr-sweep-to-right">{{ __("website.add_to_cart") }}</a>';
                                    } else {
                                        buttonsHtml += '<a href="javascript:void(0)" class="main_bttn hvr-sweep-to-right quick-add-to-cart" data-product-id="' + productId + '" data-has-addons="0">{{ __("website.add_to_cart") }}</a>';
                                    }
                                    buttonsHtml += '<a href="{{ url("/products") }}/' + productSlug + '" class="main_bttn white_bttn hvr-sweep-to-right"> {{ __("website.buy_now") }} </a>';
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
                                        $('.total_box').html('<strong>Total:</strong> ' + response.total + ' ' + (response.currency || '{{ \App\Models\Currency::getCurrentCurrencySign() }}'));
                                    }

                                    // Refresh cart sidebar
                                    if ($('#sideCart_menu .cartSide_content').length) {
                                        $('#sideCart_menu .cartSide_content').load(window.location.href + " #sideCart_menu .cartSide_content > *");
                                    }

                                    // Show success toast
                                    AppSwal.success('{{ __("website.removed_from_cart") }}');
                                } else {
                                    AppSwal.error(response.message || '{{ __("website.something_went_wrong") }}');
                                    $spinner.find('.ns-btn a').removeClass('disabled');
                                    $input.removeClass('loading');
                                }
                            },
                            error: function(xhr) {
                                let errorMessage = '{{ __("website.something_went_wrong") }}';

                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }

                                AppSwal.error(errorMessage, '{{ __("website.error") }}');
                                $spinner.find('.ns-btn a').removeClass('disabled');
                                $input.removeClass('loading');
                                $spinner.removeClass('updating');
                            }
                        });
                        return;
                    }
                    newQty = Math.max(1, currentQty - 1);
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
                                $('.total_box').html('<strong>Total:</strong> ' + response.total + ' ' + (response.currency || '{{ \App\Models\Currency::getCurrentCurrencySign() }}'));
                            }

                            // Refresh cart sidebar
                            if ($('#sideCart_menu .cartSide_content').length) {
                                $('#sideCart_menu .cartSide_content').load(window.location.href + " #sideCart_menu .cartSide_content > *");
                            }

                            // Show success toast (optional)
                            AppSwal.success('{{ __("website.cart_updated") }}');
                        } else {
                            AppSwal.error(response.message, '{{ __("website.error") }}');
                        }

                        // Re-enable buttons
                        $spinner.find('.ns-btn a').removeClass('disabled');
                        $input.removeClass('loading');
                        $spinner.removeClass('updating');
                    },
                    error: function(xhr) {
                        let errorMessage = '{{ __("website.something_went_wrong") }}';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        AppSwal.error(errorMessage, '{{ __("website.error") }}');

                        // Re-enable buttons
                        $spinner.find('.ns-btn a').removeClass('disabled');
                        $input.removeClass('loading');
                        $spinner.removeClass('updating');
                    }
                });
            }

            // Initialize spinner events for related products (only for spinners with product-id)
            // Exclude quantity spinner on product page (it has its own handler)
            $(document).off('click', '.number__spinner[data-product-id] .ns-btn a');
            $(document).on('click', '.number__spinner[data-product-id] .ns-btn a', handleSpinnerClick);

            // Product Notes Modal
            $('#productNotesModal').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const productId = button.data('product-id');
                const modal = $(this);
                modal.find('#product-note-product-id').val(productId);
            });

            // Save note
            $('#saveProductNote').on('click', function() {
                const productId = $('#product-note-product-id').val();
                const note = $('#product-note-text').val().trim();

                if (!note) {
                    AppSwal.warning('{{ __("website.please_enter_note") ?? "Please enter a note" }}');
                    return;
                }

                $.ajax({
                    url: '{{ route("website.products.notes.store", ":id") }}'.replace(':id', productId),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        note: note
                    },
                    success: function(response) {
                        if (response.success) {
                            AppSwal.success(response.message || '{{ __("website.note_added_successfully") ?? "Note added successfully" }}');
                            $('#product-note-text').val('');
                            $('#productNotesModal').modal('hide');
                        } else {
                            AppSwal.error(response.message || '{{ __("website.error") }}');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = '{{ __("website.something_went_wrong") }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        AppSwal.error(errorMessage);
                    }
                });
            });
        });
    </script>

    <!-- Product Notes Modal -->
    @if($enableProductNotes && auth('web')->check())
    <div class="modal fade" id="productNotesModal" tabindex="-1" aria-labelledby="productNotesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productNotesModalLabel">{{ __('website.product_notes') ?? 'Product Notes' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="product-note-product-id">
                    <div class="mb-3">
                        <label for="product-note-text" class="form-label">{{ __('website.add_note') ?? 'Add Note' }}</label>
                        <textarea class="form-control" id="product-note-text" rows="4" placeholder="{{ __('website.enter_your_note') ?? 'Enter your note here...' }}"></textarea>
                    </div>
                    <button type="button" class="main_bttn hvr-sweep-to-right" id="saveProductNote" style="border: none; padding: 10px 20px; font-size: 16px;">{{ __('website.save') ?? 'Save' }}</button>
                </div>
            </div>
        </div>
    </div>
    @endif

@endsection
