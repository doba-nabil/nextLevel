@extends('website.layout.master')
@section('title', __('website.cart'))
@section('body', 'bg-white')

@section('website-main')
    <!-- BreadCrumb -->
    <div class="breadCrumb_section midPadding luxury_breadcrumb">
        <div class="container px-lg-0">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('website.home') }}">{{ __('website.home') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ __('website.cart') }}
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    @if($cartProducts->isEmpty())
        <section class="pickup_section secPadding">
            <div class="container text-center py-5">
                <div class="empty_cart_luxury">
                    <i class="icon_cart_alt mb-4"></i>
                    <h3>{{ __('website.cart_empty') }}</h3>
                    <p class="text-muted mb-5">{{ __('website.looks_like_you_havent_added_anything_yet') ?? 'Your cart is feeling a bit light. Let\'s find something delicious for you!' }}</p>
                    <a href="{{ route('website.home') }}" class="luxury_checkout_btn hvr-sweep-to-right px-5">
                        {{ __('website.continue_shopping') }}
                    </a>
                </div>
            </div>
        </section>
    @else
        <!-- Cart Section -->
        <section class="pickup_section secPadding pt_sm_0 pt-0 cart_redesign">
            <div class="container px-lg-0">
                <form action="{{ route('checkout') }}" method="GET" class="DPick_form">
                    <div class="row g-4">
                        <!-- Left Column: Items and Vouchers -->
                        <div class="col-12 col-lg-8">
                            <div class="cart_main_content">
                                <!-- Order Items -->
                                <div class="DPick_formDIV premium_card mb-4">
                                    <h3 class="asideSM_title luxury_title"> 
                                        <i class="icon_cart_alt me-2"></i> {{ __('website.order_items') }} 
                                    </h3>
                                    <div class="cart_items_slider">
                                        @foreach($cartProducts as $item)
                                            <div class="cart-item-wrapper luxury_item" data-product-id="{{ $item['id'] }}">
                                                <div class="ordItem_cardNSM redesigned" data-product-id="{{ $item['id'] }}">
                                                    <div class="ordItem_thumb">
                                                        @php
                                                            $settingModel = \App\Models\Setting::getSettingModel();
                                                            $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
                                                            $productImage = !empty($item['image']) ? $item['image'] : $logoUrl;
                                                            $hasImage = !empty($item['image']);
                                                        @endphp
                                                        <img src="{{ $productImage }}" alt="{{ $item['name'] }}"
                                                             class="ordItem_img {{ !$hasImage ? 'no-product-image' : '' }}" loading="lazy" decoding="async"
                                                             onerror="this.src='{{ $logoUrl }}'; this.classList.add('no-product-image');">
                                                    </div>
                                                    <div class="ordItem_cont">
                                                        <div class="item_header_flex">
                                                            <h4 class="item_name"> {{ $item['name'] }} </h4>
                                                            <span class="remove_order removeFromCart"
                                                                  data-id="{{ $item['id'] }}" title="{{ __('website.remove') }}">
                                                                <i class="icon_close"></i>
                                                            </span>
                                                        </div>
                                                        
                                                        @if(!empty($item['addons']))
                                                            <div class="addons_list">
                                                                @foreach($item['addons'] as $addon)
                                                                    <span class="addon_pill">+ {{ $addon['name'] }}</span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        @if(!empty($item['is_box']) && !empty($item['box_addons']))
                                                            <div class="addons_list mt-1">
                                                                @foreach($item['box_addons'] as $sub)
                                                                    <div class="box_sub_item">
                                                                        <small class="text-muted d-block">{{ $sub['sub_product_name'] }}</small>
                                                                        @foreach(($sub['addons'] ?? []) as $addon)
                                                                            <span class="addon_pill ms-2">+ {{ $addon['name'] }}</span>
                                                                        @endforeach
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        
                                                        <div class="item_footer_flex">
                                                            <div class="ordItem_price"> 
                                                                {{ number_format((float)$item['price'], 3) }} 
                                                                <small>{{ \App\Models\Currency::getCurrentCurrencySign() }}</small>
                                                            </div>
                                                            <div class="number__spinner premium_spinner" data-product-id="{{ $item['id'] }}">
                                                                <span class="ns-btn">
                                                                    <a class="update-quantity"
                                                                       data-product-id="{{ $item['id'] }}"
                                                                       data-action="decrease">
                                                                        <i class="fa fa-minus"></i>
                                                                    </a>
                                                                </span>
                                                                <input type="text" class="pl-ns-value quantity-input"
                                                                       value="{{ $item['quantity'] }}" maxlength="2" readonly>
                                                                <span class="ns-btn">
                                                                    <a class="update-quantity"
                                                                       data-product-id="{{ $item['id'] }}"
                                                                       data-action="increase">
                                                                        <i class="fa fa-plus"></i>
                                                                    </a>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="item_notes_wrapper">
                                                    <textarea class="product-note luxury_textarea"
                                                              name="item_notes[{{ $item['id'] }}]"
                                                              data-product-id="{{ $item['id'] }}" rows="3"
                                                              placeholder="{{ __('website.add_note_for_this_product') }}">{{ $item['notes'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Vouchers -->
                                <div class="DPick_formDIV premium_card mb-4">
                                    <h3 class="asideSM_title luxury_title"> 
                                        <i class="icon_tag_alt me-2"></i> {{ __('website.your_vouchers') }} 
                                    </h3>

                                    @if($voucherData)
                                        <div class="alert premium_alert_success applied-voucher-alert">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong class="text-white">{{ __('website.voucher_applied') }}:</strong> 
                                                    <span class="badge bg-gold text-dark ms-2">{{ $voucherData->code }}</span>
                                                    <div class="mt-1">
                                                        <small class="text-white opacity-75">{{ __('website.discount') }}: 
                                                            <span class="text-gold fw-bold">{{ number_format((float)$voucherDiscount, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>
                                                        </small>
                                                    </div>
                                                </div>
                                                <button type="button" class="luxury_remove_btn remove-voucher">
                                                    {{ __('website.remove') }}
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="row g-3 vouchers_row" id="vouchers-container">
                                        @forelse($availableVouchers as $voucher)
                                            <div class="col-12 col-xl-6 mb-3">
                                                @php
                                                    $isSelected = $voucherData && $voucherData->code == $voucher->code;
                                                @endphp
                                                <div class="voucher-card-wrapper {{ $isSelected ? 'voucher-disabled' : '' }}"
                                                     data-voucher-code="{{ $voucher->code }}"
                                                     data-is-selected="{{ $isSelected ? '1' : '0' }}">
                                                    <div class="premium-ticket {{ $isSelected ? 'voucher-selected' : '' }}">
                                                        <div class="ticket-left">
                                                            <div class="ticket-discount">
                                                                {{ $voucher->value }}{{ $voucher->type == 'percent' ? '%' : '' }}
                                                                <small>{{ __('website.discount') }}</small>
                                                            </div>
                                                            <div class="ticket-code-wrap mt-2">
                                                                <span class="code-value">{{ $voucher->code }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="ticket-right">
                                                            <div class="ticket-meta">
                                                                @if($voucher->expire_at)
                                                                    <div class="meta-item">
                                                                        <i class="fa-regular fa-calendar-check me-2"></i>
                                                                        <span>{{ __('website.expiry') }}: {{ $voucher->expire_at->format('d M, Y') }}</span>
                                                                    </div>
                                                                @endif
                                                                @if($voucher->min_order_price > 0)
                                                                    <div class="meta-item mt-1">
                                                                        <i class="fa-solid fa-cart-shopping me-2"></i>
                                                                        <span>Min: {{ number_format((float)$voucher->min_order_price, 0) }}</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="ticket-footer mt-2 d-flex justify-content-between align-items-center">
                                                                <small class="text-muted">{{ __('website.apply') }}</small>
                                                                @if($isSelected)
                                                                    <span class="badge bg-gold text-dark">{{ __('website.applied') }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12">
                                                <p class="text-muted text-center py-3">{{ __('website.no_vouchers_available') }}</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Order Notes -->
                                <div class="DPick_formDIV premium_card mb-4">
                                    <h3 class="asideSM_title luxury_title"> 
                                        <i class="icon_pencil-edit me-2"></i> {{ __('website.order_notes') }} 
                                    </h3>
                                    <div class="accountInfo_cbody w-100">
                                        <textarea rows="3" class="luxury_textarea w-100"
                                                  name="order_notes"
                                                  placeholder="{{ __('website.add_order_notes') }}">{{ old('order_notes') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Summary (Sticky) -->
                        <div class="col-12 col-lg-4">
                            <div class="sticky_summary">
                                <div class="DPick_formDIV premium_card summary_card">
                                    <h3 class="asideSM_title luxury_title border-bottom pb-3 mb-4"> 
                                        {{ __('website.order_summary') }} 
                                    </h3>
                                    
                                    <div class="summary_details">
                                        <div class="DPick_ftotal summary_row">
                                            <span class="label"> {{ __('website.subtotal') }} </span>
                                            <span class="value" id="cart-subtotal"> 
                                                {{ number_format((float)$subtotal, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }} 
                                            </span>
                                        </div>

                                        @if($voucherDiscount > 0)
                                            <div class="DPick_ftotal summary_row discount_row">
                                                <span class="label"> {{ __('website.discount') }} </span>
                                                <span class="value text-success" id="cart-discount"> 
                                                    -{{ number_format((float)$voucherDiscount, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }} 
                                                </span>
                                            </div>
                                        @endif

                                        @php
                                            $userLocation = session('user_location');
                                            $hasCity = $userLocation && isset($userLocation['city_id']) && $userLocation['city_id'];
                                            $menuType = session('menu_type', 'delivery');
                                            $displayDeliveryCost = isset($deliveryCost) ? (float)$deliveryCost : 0;
                                        @endphp
                                        
                                        @if($hasCity && $menuType === 'delivery')
                                            <div class="DPick_ftotal summary_row" id="delivery-cost-row" style="{{ $displayDeliveryCost > 0 ? '' : 'display: none;' }}">
                                                <span class="label"> {{ __('website.delivery_cost') }} </span>
                                                <span class="value" id="cart-delivery-cost"> 
                                                    @if($displayDeliveryCost > 0)
                                                        {{ number_format($displayDeliveryCost, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}
                                                    @else
                                                        {{ __('website.free_delivery') }}
                                                    @endif
                                                </span>
                                            </div>
                                            @if($displayDeliveryCost == 0)
                                                <div class="DPick_ftotal summary_row" id="delivery-free-row">
                                                    <span class="label"> {{ __('website.delivery_cost') }} </span>
                                                    <span class="value" style="color: #28a745; font-weight: bold;"> 
                                                        {{ __('website.free_delivery') }}
                                                    </span>
                                                </div>
                                            @endif
                                        @endif

                                        <div class="total_divider"></div>

                                        <div class="DPick_ftotal summary_row final_total">
                                            <span class="label"> {{ __('website.total') }} </span>
                                            <span class="value" id="cart-total"> 
                                                {{ number_format((float)$total, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }} 
                                            </span>
                                        </div>
                                    </div>

                                    <button type="submit" class="luxury_checkout_btn hvr-sweep-to-right w-100 mt-4">
                                        {{ __('website.continue_to_checkout') }} <i class="icon_right_alt ms-2"></i>
                                    </button>
                                    
                                    <div class="secure_checkout mt-3">
                                        <i class="icon_shield"></i> {{ __('website.secure_checkout') ?? 'Secure Checkout' }}
                                    </div>
                                </div>
                                
                                <a href="{{ route('website.home') }}" class="continue_shopping_link mt-3 d-block text-center">
                                    <i class="icon_left_alt me-1"></i> {{ __('website.continue_shopping') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <!-- Related Products Section -->
        @if($relatedProducts->count() > 0)
            <section class="pickup_section wide_padding related_section">
                <div class="container px-lg-0">
                    <div class="between_flex mb-5">
                        <div class="title_wrapper">
                            <h3 class="related_title"> {{ __('website.you_can_also_add') }} </h3>
                        </div>
                    </div>
                    <div class="related_products_slider">
                        @foreach($relatedProducts as $product)
                            <div class="productOne_itemN">
                                <div class="product_cardN1">
                                    <a href="{{ route('website.products', $product->slug) }}" class="prodThumb_link">
                                        <img
                                            @php
                                                $settingModel = \App\Models\Setting::getSettingModel();
                                                $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
                                                $productImage = $product->getFirstMediaUrl('products', 'thumb');
                                                $hasImage = !empty($productImage);
                                            @endphp
                                            src="{{ $productImage ?: $logoUrl }}"
                                            class="prodThumb_img {{ !$hasImage ? 'no-product-image' : '' }}"
                                            alt="{{ $product->name }}" class="prodThumb_img" loading="lazy" decoding="async">
                                    </a>
                                    <div class="content_box">
                                        <h5 class="pro_title">
                                            <a href="{{ route('website.products', $product->slug) }}"> {{ $product->name }} </a>
                                        </h5>
                                        <div class="content_bInfo">
                                            <span class="health_status">{{ __('website.healthy') }}</span>
                                            <div
                                                class="pro_price">
                                                @php
                                                    $priceDetails = $product->getPriceDetails(session('currency'));
                                                @endphp
                                                @if($priceDetails['has_discount'])
                                                    <span class="price-before" style="text-decoration: line-through; color: #999; margin-inline-end: 8px;">
                                                        {{ number_format($priceDetails['original'], 2) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}
                                                    </span>
                                                    <span class="price-after" style="color: #28a745; font-weight: bold;">
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
                                        @if(isset($cartProductIds[$product->id]))
                                            @php
                                                $hasAddons = $product->addonGroups()->count() > 0 || $product->addons()->count() > 0;
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
                                                <input type="text" class="pl-ns-value" value="{{ $cartQuantities[$product->id] ?? 1 }}" maxlength="2" readonly tabindex="0">
                                                <span class="ns-btn">
                                                    <a data-dir="dwn" class="remove-product-btn" tabindex="0">
                                                        <i class="icon_trash"></i>
                                                    </a>
                                                </span>
                                            </div>
                                        @else
                                            <div class="buttons_wrapper w-100 product-buttons-{{ $product->id }}">
                                                @php
                                                    $hasAddons = $product->addonGroups()->count() > 0 || $product->addons()->count() > 0;
                                                @endphp
                                                @if($hasAddons)
                                                    <a href="{{ route('website.products', $product->slug) }}"
                                                       class="main_bttn hvr-sweep-to-right">{{ __('website.add_to_cart') }}</a>
                                                @else
                                                    <a href="javascript:void(0)"
                                                       class="main_bttn hvr-sweep-to-right quick-add-to-cart"
                                                       data-product-id="{{ $product->id }}"
                                                       data-has-addons="0">{{ __('website.add_to_cart') }}</a>
                                                @endif
                                                <a href="{{ route('website.products', $product->slug) }}"
                                                   class="main_bttn white_bttn hvr-sweep-to-right"> {{ __('website.buy_now') }} </a>
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
    @endif

@endsection

@section('website-footer')
    <script>
        // Slick Sliders Initialization
        $(function(){
            var is_rtl = $("html[lang='ar']").length > 0;

            // Cart Items Slider
            $('.cart_items_slider').slick({
                infinite: true,
                slidesToShow: 1,
                slidesToScroll: 1,
                rtl: is_rtl,
                dots: true,
                arrows: false,
                autoplay: false,
                speed: 500,
                adaptiveHeight: true
            });

            // Related Products Slider
            $('.related_products_slider').slick({
                infinite: true,
                slidesToShow: 4,
                slidesToScroll: 1,
                rtl: is_rtl,
                dots: false,
                arrows: false,
                autoplay: false,
                speed: 500,
                responsive: [
                    {
                        breakpoint: 991,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 1,
                        }
                    },
                    {
                        breakpoint: 767,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2,
                        }
                    },
                    {
                        breakpoint: 575,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2,
                        }
                    }
                ]
            });
        });

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
                                $('#cartCount_mobile').show();
                            }
                        }

                        const $productCard = $button.closest('.product_cardN1, .productOne_itemN');
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

                        // Update cart totals on page
                        if (response.subtotal && $('#cart-subtotal').length) {
                            $('#cart-subtotal').text(response.subtotal + ' ' + (response.currency || '{{ \App\Models\Currency::getCurrentCurrencySign() }}'));
                        }
                        if (response.discount && $('#cart-discount').length) {
                            $('#cart-discount').text('-' + response.discount + ' ' + (response.currency || '{{ \App\Models\Currency::getCurrentCurrencySign() }}'));
                            $('#cart-discount').closest('.discount_row').show();
                        }
                        if (response.delivery_cost !== undefined) {
                            const deliveryCost = parseFloat(response.delivery_cost);
                            if (deliveryCost > 0) {
                                if ($('#cart-delivery-cost').length) {
                                    $('#cart-delivery-cost').text(response.delivery_cost + ' ' + (response.currency || '{{ \App\Models\Currency::getCurrentCurrencySign() }}'));
                                    $('#cart-delivery-cost').closest('#delivery-cost-row').show();
                                }
                                if ($('#delivery-free-row').length) {
                                    $('#delivery-free-row').hide();
                                }
                            } else {
                                if ($('#cart-delivery-cost').length) {
                                    $('#cart-delivery-cost').closest('#delivery-cost-row').hide();
                                }
                                if ($('#delivery-free-row').length) {
                                    $('#delivery-free-row').show();
                                } else {
                                    // Create free delivery row if it doesn't exist
                                    const freeDeliveryRow = '<div class="DPick_ftotal summary_row" id="delivery-free-row"><span class="label">{{ __("website.delivery_cost") }}</span><span class="value" style="color: #28a745; font-weight: bold;">{{ __("website.free_delivery") }}</span></div>';
                                    if ($('#delivery-cost-row').length) {
                                        $('#delivery-cost-row').after(freeDeliveryRow);
                                    } else if ($('.total_divider').length) {
                                        $('.total_divider').before(freeDeliveryRow);
                                    }
                                }
                            }
                        }
                        if (response.total && $('#cart-total').length) {
                            $('#cart-total').text(response.total + ' ' + (response.currency || '{{ \App\Models\Currency::getCurrentCurrencySign() }}'));
                        }

                        SwalConfig.success('{{ __("website.added_to_cart") }}');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        SwalConfig.error(response.message || '{{ __("website.something_went_wrong") }}');
                        $button.removeClass('disabled').html(originalButtonHtml);
                    }
                },
                error: function(xhr) {
                    SwalConfig.error('{{ __("website.something_went_wrong") }}');
                    $button.removeClass('disabled').html(originalButtonHtml);
                }
            });
        });

        // Universal Spinner Handler for Related Products - AJAX without reload
        function handleSpinnerClick(e) {
            e.preventDefault();
            const $button = $(this);
            const $spinner = $button.closest('.number__spinner');
            if ($spinner.hasClass('updating')) return;

            if ($button.data('dir')) {
                const $input = $spinner.find('.pl-ns-value');
                let productId = parseInt($spinner.data('product-id')) || 0;
                const direction = $button.data('dir');
                let currentQty = parseInt($input.val()) || 1;
                let newQty = direction === 'up' ? currentQty + 1 : Math.max(0, currentQty - 1);

                if (newQty === 0) {
                    // Remove product from cart
                    $.ajax({
                        url: "{{ route('cart.remove') }}",
                        method: "POST",
                        data: { _token: "{{ csrf_token() }}", product_id: productId },
                        success: function(response) { 
                            if (response.status) {
                                // Remove item from cart page
                                $spinner.closest('.cart-item-wrapper, .luxury_item').fadeOut(300, function() {
                                    $(this).remove();
                                    // Check if cart is empty
                                    if ($('.cart-item-wrapper, .luxury_item').length === 0) {
                                        location.reload(); // Reload only if cart is empty
                                    } else {
                                        // Update totals
                                        updateCartTotals(response);
                                    }
                                });
                            }
                        }
                    });
                } else {
                    $spinner.addClass('updating');
                    $.ajax({
                        url: "{{ route('cart.update.quantity') }}",
                        method: "POST",
                        data: { _token: "{{ csrf_token() }}", product_id: productId, quantity: newQty },
                        success: function(response) {
                            if (response.status) {
                                // Update quantity
                                $input.val(newQty);
                                
                                // Update cart totals
                                updateCartTotals(response);
                                
                                // Update side cart if exists
                                if ($('#sideCart_menu .cartSide_content').length) {
                                    $('#sideCart_menu .cartSide_content').load(window.location.href + " #sideCart_menu .cartSide_content > *");
                                }
                            }
                        },
                        error: function() {
                            SwalConfig.error('{{ __("website.something_went_wrong") }}');
                        },
                        complete: function() {
                            $spinner.removeClass('updating');
                        }
                    });
                }
            }
        }
        $(document).on('click', '.ns-btn a', handleSpinnerClick);
        
        // Helper function to update cart totals
        function updateCartTotals(response) {
            if (response.subtotal && $('#cart-subtotal').length) {
                $('#cart-subtotal').html(response.subtotal + ' ' + response.currency);
            }
            
            if (response.discount !== undefined) {
                if (response.discount > 0) {
                    if ($('#cart-discount').length) {
                        $('#cart-discount').html('-' + response.discount + ' ' + response.currency);
                        $('#cart-discount').closest('.discount_row').show();
                    } else {
                        let discountRow = '<div class="DPick_ftotal summary_row discount_row">' +
                            '<span class="label">{{ __("website.discount") }}</span>' +
                            '<span class="value text-success" id="cart-discount">-' + response.discount + ' ' + response.currency + '</span>' +
                            '</div>';
                        $('#cart-subtotal').closest('.summary_row').after(discountRow);
                    }
                } else {
                    $('#cart-discount').closest('.discount_row').hide();
                }
            }
            
            if (response.delivery_cost !== undefined) {
                if (response.delivery_cost > 0) {
                    if ($('#cart-delivery-cost').length) {
                        $('#cart-delivery-cost').html(response.delivery_cost + ' ' + response.currency);
                        $('#delivery-cost-row').show();
                        $('#delivery-free-row').hide();
                    }
                } else {
                    if ($('#cart-delivery-cost').length) {
                        $('#delivery-cost-row').hide();
                        if (!$('#delivery-free-row').length) {
                            let freeDeliveryRow = '<div class="DPick_ftotal summary_row" id="delivery-free-row">' +
                                '<span class="label">{{ __("website.delivery_cost") }}</span>' +
                                '<span class="value" style="color: #28a745; font-weight: bold;">{{ __("website.free_delivery") }}</span>' +
                                '</div>';
                            $('#delivery-cost-row').after(freeDeliveryRow);
                        } else {
                            $('#delivery-free-row').show();
                        }
                    }
                }
            }
            
            if (response.total && $('#cart-total').length) {
                $('#cart-total').html(response.total + ' ' + response.currency);
            }
            
            // Update cart count in header
            if (response.count !== undefined) {
                $('.cart_badge, .cart_badge_mobile').text(response.count);
                if (response.count == 0) {
                    $('.cart_badge, .cart_badge_mobile').hide();
                } else {
                    $('.cart_badge, .cart_badge_mobile').show();
                }
            }
        }

        // Remove from cart - AJAX without reload
        $(document).on('click', '.removeFromCart', function (e) {
            e.preventDefault();
            let $removeBtn = $(this);
            let productId = $removeBtn.data('id');
            Swal.fire({
                title: "{{ __('website.confirm_delete') }}",
                text: "{{ __('website.remove_from_cart_question') }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#000",
                cancelButtonColor: "#eee",
                confirmButtonText: "{{ __('website.yes_delete') }}",
                cancelButtonText: "{{ __('website.cancel') }}"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('cart.remove') }}",
                        method: "POST",
                        data: { _token: "{{ csrf_token() }}", product_id: productId },
                        success: function (response) { 
                            if (response.status) {
                                // Remove item from cart page
                                $removeBtn.closest('.cart-item-wrapper, .luxury_item').fadeOut(300, function() {
                                    $(this).remove();
                                    // Check if cart is empty
                                    if (response.cart_empty || $('.cart-item-wrapper, .luxury_item').length === 0) {
                                        location.reload(); // Reload only if cart is empty
                                    } else {
                                        // Update totals
                                        updateCartTotals(response);
                                    }
                                });
                            }
                        }
                    });
                }
            });
        });

        // Update quantity for cart items (Slider Version) - AJAX without reload
        $(document).on('click', '.update-quantity', function (e) {
            e.preventDefault();
            let $button = $(this);
            let productId = $button.data('product-id');
            let action = $button.data('action');
            let $spinner = $button.closest('.premium_spinner');
            let $input = $spinner.find('.quantity-input');
            let currentQty = parseInt($input.val()) || 1;
            let newQty = action === 'increase' ? currentQty + 1 : Math.max(1, currentQty - 1);

            if (newQty < 1 || $button.prop('disabled')) return;

            $button.prop('disabled', true);
            $input.addClass('loading');
            $spinner.addClass('updating');

            $.ajax({
                url: "{{ route('cart.update.quantity') }}",
                method: "POST",
                data: { _token: "{{ csrf_token() }}", product_id: productId, quantity: newQty },
                success: function (response) {
                    if (response.status) {
                        // Update quantity input
                        $input.val(newQty).removeClass('loading');
                        
                        // Update item price
                        let $itemWrapper = $spinner.closest('.cart-item-wrapper');
                        let $itemPrice = $itemWrapper.find('.ordItem_price');
                        if ($itemPrice.length && response.item_price) {
                            $itemPrice.html(response.item_price + ' <small>' + response.currency + '</small>');
                        }
                        
                        // Update cart summary
                        if (response.subtotal && $('#cart-subtotal').length) {
                            $('#cart-subtotal').html(response.subtotal + ' ' + response.currency);
                        }
                        
                        if (response.discount !== undefined) {
                            if (response.discount > 0) {
                                if ($('#cart-discount').length) {
                                    $('#cart-discount').html('-' + response.discount + ' ' + response.currency);
                                    $('#cart-discount').closest('.discount_row').show();
                                } else {
                                    // Add discount row if it doesn't exist
                                    let discountRow = '<div class="DPick_ftotal summary_row discount_row">' +
                                        '<span class="label">{{ __("website.discount") }}</span>' +
                                        '<span class="value text-success" id="cart-discount">-' + response.discount + ' ' + response.currency + '</span>' +
                                        '</div>';
                                    $('#cart-subtotal').closest('.summary_row').after(discountRow);
                                }
                            } else {
                                $('#cart-discount').closest('.discount_row').hide();
                            }
                        }
                        
                        if (response.delivery_cost !== undefined) {
                            if (response.delivery_cost > 0) {
                                if ($('#cart-delivery-cost').length) {
                                    $('#cart-delivery-cost').html(response.delivery_cost + ' ' + response.currency);
                                    $('#delivery-cost-row').show();
                                    $('#delivery-free-row').hide();
                                }
                            } else {
                                if ($('#cart-delivery-cost').length) {
                                    $('#delivery-cost-row').hide();
                                    if (!$('#delivery-free-row').length) {
                                        let freeDeliveryRow = '<div class="DPick_ftotal summary_row" id="delivery-free-row">' +
                                            '<span class="label">{{ __("website.delivery_cost") }}</span>' +
                                            '<span class="value" style="color: #28a745; font-weight: bold;">{{ __("website.free_delivery") }}</span>' +
                                            '</div>';
                                        $('#delivery-cost-row').after(freeDeliveryRow);
                                    } else {
                                        $('#delivery-free-row').show();
                                    }
                                }
                            }
                        }
                        
                        if (response.total && $('#cart-total').length) {
                            $('#cart-total').html(response.total + ' ' + response.currency);
                        }
                        
                        // Update cart count in header if exists
                        if (response.count !== undefined) {
                            $('.cart_badge, .cart_badge_mobile').text(response.count);
                            if (response.count == 0) {
                                $('.cart_badge, .cart_badge_mobile').hide();
                            } else {
                                $('.cart_badge, .cart_badge_mobile').show();
                            }
                        }
                        
                        // Update side cart if exists
                        if ($('#sideCart_menu .cartSide_content').length) {
                            $('#sideCart_menu .cartSide_content').load(window.location.href + " #sideCart_menu .cartSide_content > *");
                        }
                    } else {
                        $button.prop('disabled', false);
                        $input.removeClass('loading');
                        $spinner.removeClass('updating');
                        SwalConfig.error(response.message || '{{ __("website.error") }}');
                    }
                },
                error: function(xhr) {
                    $button.prop('disabled', false);
                    $input.removeClass('loading');
                    $spinner.removeClass('updating');
                    SwalConfig.error('{{ __("website.something_went_wrong") }}');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $input.removeClass('loading');
                    $spinner.removeClass('updating');
                }
            });
        });

        // Voucher Application
        $(document).on('click', '.voucher-card-wrapper', function (e) {
            e.preventDefault();
            let voucherCode = $(this).data('voucher-code');
            let isSelected = $(this).data('is-selected') == '1';
            let url = isSelected ? "{{ route('cart.remove.voucher') }}" : "{{ route('cart.apply.voucher') }}";
            let data = isSelected ? { _token: "{{ csrf_token() }}" } : { _token: "{{ csrf_token() }}", code: voucherCode };

            $.ajax({
                url: url,
                method: "POST",
                data: data,
                success: function (response) {
                    if (response.status) location.reload();
                    else SwalConfig.error(response.message);
                }
            });
        });

        $(document).on('click', '.remove-voucher', function (e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('cart.remove.voucher') }}",
                method: "POST",
                data: { _token: "{{ csrf_token() }}" },
                success: function (response) { if (response.status) location.reload(); }
            });
        });

        // Save Notes
        $(document).on('blur', '.product-note', function() {
            let productId = $(this).data('product-id');
            let notes = $(this).val();
            $.ajax({
                url: "{{ route('cart.update.notes') }}",
                method: "POST",
                data: { _token: "{{ csrf_token() }}", product_id: productId, notes: notes }
            });
        });
    </script>
@endsection
