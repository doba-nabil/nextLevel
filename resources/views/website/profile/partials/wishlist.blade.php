<div class="proCont_wrapper">
    <h3 class="profile-section-title">
        <i class="fa-solid fa-heart gold-icon"></i>
        {{ __('website.my_wishlist') ?? 'My Wishlist' }}
    </h3>

    @if($favorites && $favorites->count() > 0)
        <div class="row g-4">
            @foreach($favorites as $favorite)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="profile-content-card p-0 product-wishlist-card">
                        <div class="product-thumb-wrapper">
                            <a href="{{ route('website.products', $favorite->product->id) }}">
                                @php
                                    $settingModel = \App\Models\Setting::getSettingModel();
                                    $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
                                    $productImage = $favorite->product->getFirstMediaUrl('products');
                                @endphp
                                <img src="{{ $productImage ?: $logoUrl }}" alt="{{ $favorite->product->name }}" class="product-thumb">
                            </a>
                            <button class="remove-wishlist-btn remove_favorite_btn" data-product-id="{{ $favorite->product->id }}">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="product-info p-3">
                            <h5 class="product-name">
                                <a href="{{ route('website.products', $favorite->product->id) }}">{{ $favorite->product->name }}</a>
                            </h5>
                            <div class="product-price">
                                @php
                                    $priceDetails = $favorite->product->getPriceDetails(session('currency'));
                                @endphp
                                @if($priceDetails['has_discount'])
                                    <span class="price-after">
                                        {{ number_format($priceDetails['discounted'], 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}
                                    </span>
                                    <span class="price-before ms-2">
                                        {{ number_format($priceDetails['original'], 3) }}
                                    </span>
                                @else
                                    <span class="price-after">
                                        {{ number_format($priceDetails['discounted'], 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="profile-content-card text-center py-5">
            <div class="empty-icon mb-4">
                <i class="fa-solid fa-heart-crack" style="font-size: 60px; color: #f1f1f1;"></i>
            </div>
            <h4>{{ __('website.no_favorites_found') }}</h4>
            <p class="text-muted">{{ __('website.start_shopping_to_add_favorites') ?? 'Start shopping to add items to your wishlist.' }}</p>
            <a href="{{ route('website.home') }}" class="main_bttn mid_bttn mt-3">{{ __('website.start_shopping') }}</a>
        </div>
    @endif
</div>


