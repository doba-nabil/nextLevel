@extends('website.layout.master')
@section('title', __('website.search'))
@section('body', false)
@section('website-main')
    <!-- Content -->
    <div class="breadCrumb_section midPadding">
        <div class="container px-lg-0">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('website.home') }}">{{ __('website.home') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> {{ __('website.search') }} </li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="pickup_section secPadding pt-0">
        <div class="container">
            <div class="row">

                <div class="col-12 col-lg-12">
                    <div class="row incat_row">
                        @if(isset($products) && $products->count())
                            @foreach($products as $product)
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="product_cardN1 wow slideInDown" data-wow-offset="100"
                                         data-wow-duration="1.5s">
                                        @php
                                            $settingModel = \App\Models\Setting::getSettingModel();
                                            $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
                                            $productImage = $product->getFirstMediaUrl('products');
                                            $hasImage = !empty($productImage);
                                        @endphp
                                        <a href="{{ route('website.products',$product->slug) }}" class="prodThumb_link">
                                            <img src="{{ $productImage ?: $logoUrl }}" alt=""
                                                 class="prodThumb_img {{ !$hasImage ? 'no-product-image' : '' }}">
                                        </a>
                                        <div class="content_box">
                                            <h5 class="pro_title"><a
                                                    href="{{ route('website.products',$product->slug) }}">
                                                    {{ $product->name }} </a>
                                            </h5>
                                            <div class="content_bInfo">
                                                <span class="health_status">{{ __('website.healthy') }}</span>
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
                                                                <span> {{ $definition->name }} </span>
                                                                <strong>{{ rtrim(rtrim(number_format((float)$definition->pivot->value, 2, '.', ''), '0'), '.') }}{{ $definition->unit }}</strong>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="buttons_wrapper w-100 ">
                                                <a href="#" class="main_bttn hvr-sweep-to-right">
                                                    + {{ __('website.add') }} </a>
                                                <a href="{{ route('website.products',$product->slug) }}"
                                                   class="main_bttn white_bttn hvr-sweep-to-right">
                                                    {{ __('website.buy_now') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <h4 class="alert alert-warning rounded-0 border-0">{{ __('website.no_products_search') }}</h4>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--/ Content -->
@endsection
