<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Location;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $menuType = session('menu_type', 'delivery');
        
        // For pickup orders, validate branch selection
        if ($menuType === 'pickup') {
            $pickupBranchId = session('pickup_branch_id');
            if (!$pickupBranchId) {
                return response()->json([
                    'status' => false,
                    'message' => __('website.please_select_branch_first') ?? 'من فضلك اختر الفرع أولًا'
                ], 422);
            }
            
            // Verify branch is active
            $branch = \App\Models\Branch::where('active', 1)->with('workingHours')->find($pickupBranchId);
            if (!$branch) {
                session()->forget('pickup_branch_id');
                return response()->json([
                    'status' => false,
                    'message' => __('website.please_select_branch_first') ?? 'من فضلك اختر الفرع أولًا'
                ], 422);
            }
            
            // Check if branch is currently open based on working hours
            if (!$branch->isCurrentlyOpen()) {
                return response()->json([
                    'status' => false,
                    'message' => __('website.restaurant_is_closed') ?? 'المطعم مغلق حالياً ولا يمكن إضافة منتجات للكارت'
                ], 422);
            }
        } else {
            // For delivery orders, check if any branch in the city is open
            $userLocation = session('user_location');
            $cityId = $userLocation['city_id'] ?? null;
            
            if ($cityId) {
                // Get all active branches in the city
                $branches = \App\Models\Branch::where('active', 1)
                    ->whereHas('cities', function($q) use ($cityId) {
                        $q->where('locations.id', $cityId);
                    })
                    ->with('workingHours')
                    ->get();
                
                // Check if at least one branch is open
                $hasOpenBranch = false;
                foreach ($branches as $branch) {
                    if ($branch->isCurrentlyOpen()) {
                        $hasOpenBranch = true;
                        break;
                    }
                }
                
                if (!$hasOpenBranch && $branches->isNotEmpty()) {
                    return response()->json([
                        'status' => false,
                        'message' => __('website.restaurant_is_closed') ?? 'المطعم مغلق حالياً ولا يمكن إضافة منتجات للكارت'
                    ], 422);
                }
            }
        }
        
        // Validate city selection (for delivery or product availability check)
        $userLocation = session('user_location');
        if (!$userLocation || !isset($userLocation['city_id']) || !$userLocation['city_id']) {
            return response()->json([
                'status' => false,
                'message' => __('website.please_select_city_before_adding_to_cart') ?? 'Please select a city before adding items to cart'
            ], 422);
        }
        
        $product = Product::where('active', true)
            ->findOrFail($request->product_id);
        
        // Check if product is available in selected city
        $cityId = $userLocation['city_id'];
        
        // For pickup orders, check specific branch
        if ($menuType === 'pickup') {
            $pickupBranchId = session('pickup_branch_id');
            if ($pickupBranchId) {
                $isAvailable = $product->branches()
                    ->where('branches.id', $pickupBranchId)
                    ->where('branches.active', true)
                    ->where('product_branches.status', 'available')
                    ->exists();
                
                if (!$isAvailable) {
                    return response()->json([
                        'status' => false,
                        'message' => __('website.product_not_available_in_branch') ?? 'هذا المنتج غير متاح في الفرع المختار حالياً'
                    ], 422);
                }
            }
        } else {
            // For delivery orders, check if product is available in any branch in the city
            $isAvailableInCity = $product->branches()
                ->whereHas('cities', function($q) use ($cityId) {
                    $q->where('locations.id', $cityId);
                })
                ->where('branches.active', true)
                ->where('product_branches.status', 'available')
                ->exists();
            
            if (!$isAvailableInCity) {
                return response()->json([
                    'status' => false,
                    'message' => __('website.product_not_available_in_selected_city') ?? 'هذا المنتج غير متاح في المدينة المختارة حالياً'
                ], 422);
            }
        }
        
        $quantity = (int) $request->quantity;
        
        if ($quantity < 1) {
            return response()->json([
                'status' => false,
                'message' => __('website.quantity_min_one')
            ]);
        }
        
        $addons = $request->input('addons', []);
        $isBox = (bool) $request->input('is_box', false);
        $boxAddons = $request->input('box_addons', []); // associative: [subProductId => [addonIds]]
        // New preferred nested structure from client: subproducts: [{product_id, addons: []}]
        $subproducts = $request->input('subproducts', []);
        if (empty($subproducts) && !empty($boxAddons) && is_array($boxAddons)) {
            // Derive subproducts array from box_addons map for backward compatibility
            $subproducts = [];
            foreach ($boxAddons as $spId => $addonIds) {
                $subproducts[] = [
                    'product_id' => (int) $spId,
                    'addons' => array_map('intval', (array) $addonIds),
                ];
            }
        }
        
        // Validate box products if it's a box
        if ($isBox && empty($subproducts)) {
            return response()->json([
                'status' => false,
                'message' => __('website.please_select_at_least_one_product')
            ]);
        }
        
        // Flat list of subproduct ids for quick reference if needed
        $boxProducts = array_values(array_unique(array_map(function ($sp) { return (int) ($sp['product_id'] ?? 0); }, (array) $subproducts)));

        $cart = session()->get('cart', []);

        $unitPrice = (float) $product->getCurrentPrice(session('currency'));

        foreach ($addons as $addonId) {
            $addon = Addon::where('active', 1)->find($addonId);
            if ($addon) {
                $unitPrice += (float) $addon->getCurrentPrice(session('currency'));
            }
        }
        if ($isBox && is_array($subproducts)) {
            foreach ($subproducts as $sp) {
                $addonIds = (array) ($sp['addons'] ?? []);
                foreach ($addonIds as $addonId) {
                    $addon = Addon::where('active', 1)->find($addonId);
                    if ($addon) {
                        $unitPrice += (float) $addon->getCurrentPrice(session('currency'));
                    }
                }
            }
        }
        $found = false;

        foreach ($cart as &$item) {
            if (
                $item['product_id'] == $product->id &&
                collect($item['addons'])->sort()->values()->toArray() === collect($addons)->sort()->values()->toArray() &&
                (bool) ($item['is_box'] ?? false) === $isBox &&
                $this->subproductsEqual($item['subproducts'] ?? [], $subproducts)
            ) {
                $item['quantity'] = $quantity;
                $item['price'] = (float) ($unitPrice * $quantity);
                if ($isBox) {
                    $item['box_products'] = $boxProducts;
                    $item['subproducts'] = $subproducts;
                }
                $found = true;
                break;
            }
        }
        if (! $found) {
            $cart[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'addons' => $addons,
                'is_box' => $isBox,
                // Keep legacy map for compatibility; primary source is subproducts
                'box_addons' => $isBox ? $boxAddons : [],
                'box_products' => $isBox ? $boxProducts : [],
                'subproducts' => $isBox ? $subproducts : [],
                'price' => (float) ($unitPrice * $quantity),
            ];
        }

        session()->put('cart', $cart);

        // Calculate cart totals
        $cartProducts = collect($cart)->map(function ($item) {
            return [
                'price' => (float) $item['price']
            ];
        });
        $subtotal = (float) $cartProducts->sum('price');

        // Recalculate voucher discount
        $appliedVoucher = session('applied_voucher');
        $voucherDiscount = 0;
        if ($appliedVoucher) {
            $voucher = \App\Models\Coupon::where('code', $appliedVoucher['code'])
                ->where('active', 1)
                ->first();
            if ($voucher) {
                $userId = auth('web')->id();
                $minOrderPrice = (float) $voucher->min_order_price;
                if ($voucher->isValidForUser($userId) && $subtotal >= $minOrderPrice) {
                    if ($voucher->type === 'percent') {
                        $voucherDiscount = $subtotal * ((float) $voucher->value / 100);
                    } else {
                        $voucherDiscount = min((float) $voucher->value, $subtotal);
                    }
                }
            }
        }

        // Calculate delivery cost
        $deliveryCost = $this->calculateDeliveryCost();
        
        $total = (float) max(0, $subtotal - $voucherDiscount + $deliveryCost);

        return response()->json([
            'status' => true,
            'message' => '',
            'total' => number_format((float)($unitPrice * $quantity), 3),
            'count' => count($cart),
            'cart_subtotal' => number_format($subtotal, 3),
            'cart_discount' => number_format($voucherDiscount, 3),
            'delivery_cost' => number_format($deliveryCost, 3),
            'cart_total' => number_format($total, 3),
            'currency' => session('currency', 'KD')
        ]);
    }

    private function boxAddonsEqual($a, $b): bool
    {
        if (!is_array($a) || !is_array($b)) return false;
        ksort($a);
        ksort($b);
        if (array_keys($a) !== array_keys($b)) return false;
        foreach ($a as $key => $arr) {
            $arr1 = collect($arr)->sort()->values()->toArray();
            $arr2 = collect($b[$key] ?? [])->sort()->values()->toArray();
            if ($arr1 !== $arr2) return false;
        }
        return true;
    }

    public function newCart()
    {
        $cart = session('cart', []);
        
        // Validate cart items against current city
        $userLocation = session('user_location');
        if ($userLocation && isset($userLocation['city_id']) && $userLocation['city_id']) {
            $cityId = $userLocation['city_id'];
            $validCart = [];
            foreach ($cart as $item) {
                $product = Product::where('active', true)
                    ->find($item['product_id'] ?? null);
                
                if (!$product) {
                    continue;
                }

                // Check if product is available in selected city with status check
                $isAvailableInCity = $product->branches()
                    ->whereHas('cities', function($q) use ($cityId) {
                        $q->where('locations.id', $cityId);
                    })
                    ->where('branches.active', true)
                    ->where('product_branches.status', 'available')
                    ->exists();

                if ($isAvailableInCity) {
                    $validCart[] = $item;
                }
            }
            
            // Update cart if items were removed
            if (count($validCart) !== count($cart)) {
                session()->put('cart', $validCart);
                $cart = $validCart;
                // Clear voucher if cart becomes empty
                if (empty($validCart)) {
                    session()->forget('applied_voucher');
                }
            }
        }
        
        $cartProducts = collect($cart)->map(function ($item) {
            $product = Product::where('active', true)
                ->with('definitions')
                ->find($item['product_id']);
            if (!$product) return null;

            // Cast to float to ensure numeric operations
            $price = (float) $item['price'];
            $quantity = (int) $item['quantity'];
            $unitPrice = $quantity > 0 ? $price / $quantity : 0;
            $addonsData = $this->getAddonsData($item['addons'] ?? []);
            // Prefer subproducts nested structure; fall back to box_addons
            $subproductsData = $this->getSubproductsData($item['subproducts'] ?? [], $item['box_addons'] ?? []);
            $boxProductsList = $this->getBoxProductsList($item['box_products'] ?? []);

            // Get product image with fallback
            $productImage = $product->getFirstMediaUrl('products', 'thumb');
            if (empty($productImage)) {
                $settingModel = \App\Models\Setting::getSettingModel();
                $productImage = $settingModel && $settingModel->getFirstMediaUrl('logo') 
                    ? $settingModel->getFirstMediaUrl('logo') 
                    : asset('website/assets/img/logo.png');
            }

            return [
                'id' => $item['product_id'],
                'slug' => $product->slug,
                'name' => $product->name,
                'image' => $productImage,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'price' => $price,
                'addons' => $addonsData,
                'is_box' => (bool) ($item['is_box'] ?? false),
                'subproducts' => $subproductsData,
                'box_addons' => $subproductsData, // For backward compatibility with view
                'box_products' => $boxProductsList,
                'notes' => $item['notes'] ?? '',
                'product' => $product
            ];
        })->filter();

        $subtotal = (float) $cartProducts->sum('price');

        // Get applied voucher from session
        $appliedVoucher = session('applied_voucher');
        $voucherDiscount = 0;
        $voucherData = null;

        if ($appliedVoucher) {
            $voucher = Coupon::where('code', $appliedVoucher['code'])
                ->where('active', 1)
                ->first();
            if ($voucher) {
                $userId = auth('web')->id();
                $minOrderPrice = (float) $voucher->min_order_price;
                if ($voucher->isValidForUser($userId) && $subtotal >= $minOrderPrice) {
                    $voucherData = $voucher;
                    if ($voucher->type === 'percent') {
                        $voucherDiscount = $subtotal * ((float) $voucher->value / 100);
                    } else {
                        $voucherDiscount = min((float) $voucher->value, $subtotal);
                    }
                }
            }
        }

        // Calculate delivery cost
        $deliveryCost = $this->calculateDeliveryCost();
        
        $total = (float) max(0, $subtotal - $voucherDiscount + $deliveryCost);

        // Get available vouchers for the user
        $userId = auth('web')->id();
        $availableVouchers = Coupon::where('active', 1)
            ->where(function($q) use ($userId) {
                $q->whereNull('user_id')->orWhere('user_id', $userId);
            })
            ->where(function($q) {
                $q->whereNull('expire_at')->orWhere('expire_at', '>=', now());
            })
            ->where(function($q) {
                $q->whereNull('usage_limit')->orWhere('usage_limit', '>', 0);
            })
            ->get();

        // Get related products for "you can also add" section - optimized query with city filter
        $userLocation = session('user_location');
        $cityId = ($userLocation && isset($userLocation['city_id']) && $userLocation['city_id']) 
            ? (int) $userLocation['city_id'] 
            : null;
        
        $relatedProducts = Product::where('active', true)
            ->where('is_box', 0)
            ->when($cityId, function($query) use ($cityId) {
                $query->whereHas('branches.cities', function($q) use ($cityId) {
                    $q->where('locations.id', $cityId);
                });
            })
            ->with(['definitions', 'addonGroups' => function($q) {
                $q->where('active', 1);
            }, 'addons' => function($q) {
                $q->where('active', 1);
            }])
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // Get cart product IDs and quantities for spinner functionality
        $cartProductIds = collect($cart)->pluck('product_id', 'product_id')->toArray();
        $cartQuantities = collect($cart)->mapWithKeys(function ($item) {
            return [$item['product_id'] => $item['quantity']];
        })->toArray();

        return view('website.cart.new_cart', compact(
            'cartProducts',
            'subtotal',
            'voucherDiscount',
            'deliveryCost',
            'total',
            'availableVouchers',
            'voucherData',
            'relatedProducts',
            'cartProductIds',
            'cartQuantities'
        ));
    }

    public function applyVoucher(Request $request)
    {
        $code = $request->input('code');
        $voucher = Coupon::where('code', $code)
            ->where('active', 1)
            ->first();

        if (!$voucher) {
            return response()->json([
                'status' => false,
                'message' => __('website.voucher_not_found')
            ]);
        }

        $userId = auth('web')->id();
        if (!$voucher->isValidForUser($userId)) {
            return response()->json([
                'status' => false,
                'message' => __('website.voucher_invalid')
            ]);
        }

        // Check cart total - cast to float
        $cart = session('cart', []);
        $subtotal = (float) collect($cart)->map(function($item) {
            return (float) $item['price'];
        })->sum();

        $minOrderPrice = (float) $voucher->min_order_price;
        if ($subtotal < $minOrderPrice) {
            return response()->json([
                'status' => false,
                'message' => __('website.voucher_min_order', ['amount' => number_format($minOrderPrice, 3)])
            ]);
        }

        // Apply voucher
        session(['applied_voucher' => [
            'code' => $voucher->code,
            'type' => $voucher->type,
            'value' => $voucher->value,
        ]]);

        return response()->json([
            'status' => true,
            'message' => __('website.voucher_applied'),
            'voucher' => $voucher,
            'currency' => Currency::getCurrentCurrencySign()
        ]);
    }

    public function removeVoucher()
    {
        session()->forget('applied_voucher');
        return response()->json([
            'status' => true,
            'message' => __('website.voucher_removed'),
            'currency' => Currency::getCurrentCurrencySign()
        ]);
    }

    public function setOrderType(Request $request)
    {
        $orderType = $request->input('order_type', 'delivery');
        session(['order_type' => $orderType]);

        return response()->json([
            'status' => true,
            'order_type' => $orderType
        ]);
    }

    public function updateQuantity(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $quantity = (int) $request->input('quantity', 1);

        if ($quantity < 1) {
            return response()->json([
                'status' => false,
                'message' => __('website.quantity_min_one')
            ]);
        }

        $cart = session('cart', []);
        $found = false;
        $updatedItemPrice = 0;

        foreach ($cart as &$item) {
            // Ensure both are integers for proper comparison
            $itemProductId = (int) ($item['product_id'] ?? 0);
            
            if ($itemProductId === $productId) {
                $product = Product::where('active', true)->find($productId);
                if (!$product) {
                    continue;
                }

                $unitPrice = (float) $product->getCurrentPrice(session('currency'));
                $addonIds = $item['addons'] ?? [];

                foreach ($addonIds as $addonId) {
                    $addon = Addon::where('active', 1)->find($addonId);
                    if ($addon) {
                        $unitPrice += (float) $addon->getCurrentPrice(session('currency'));
                    }
                }

                if (!empty($item['is_box'])) {
                    // Prefer nested subproducts structure
                    if (!empty($item['subproducts']) && is_array($item['subproducts'])) {
                        foreach ($item['subproducts'] as $sp) {
                            foreach ((array) ($sp['addons'] ?? []) as $addonId) {
                                $addon = Addon::where('active', 1)->find($addonId);
                                if ($addon) {
                                    $unitPrice += (float) $addon->getCurrentPrice(session('currency'));
                                }
                            }
                        }
                    } elseif (!empty($item['box_addons']) && is_array($item['box_addons'])) {
                        // Legacy fallback
                        foreach ($item['box_addons'] as $subProductId => $boxAddonIds) {
                            if (!is_array($boxAddonIds)) continue;
                            foreach ($boxAddonIds as $addonId) {
                                $addon = Addon::where('active', 1)->find($addonId);
                                if ($addon) {
                                    $unitPrice += (float) $addon->getCurrentPrice(session('currency'));
                                }
                            }
                        }
                    }
                }

                $item['quantity'] = $quantity;
                $updatedItemPrice = (float) ($unitPrice * $quantity);
                $item['price'] = $updatedItemPrice;
                $found = true;
                break;
            }
        }

        if (!$found) {
            return response()->json([
                'status' => false,
                'message' => __('website.product_not_in_cart')
            ]);
        }

        session()->put('cart', $cart);

        // Calculate updated totals
        $cartProducts = collect($cart)->map(function ($item) {
            return [
                'price' => (float) $item['price']
            ];
        });

        $subtotal = (float) $cartProducts->sum('price');

        // Recalculate voucher discount
        $appliedVoucher = session('applied_voucher');
        $voucherDiscount = 0;

        if ($appliedVoucher) {
            $voucher = Coupon::where('code', $appliedVoucher['code'])
                ->where('active', 1)
                ->first();
            if ($voucher) {
                $userId = auth('web')->id();
                $minOrderPrice = (float) $voucher->min_order_price;
                if ($voucher->isValidForUser($userId) && $subtotal >= $minOrderPrice) {
                    if ($voucher->type === 'percent') {
                        $voucherDiscount = $subtotal * ((float) $voucher->value / 100);
                    } else {
                        $voucherDiscount = min((float) $voucher->value, $subtotal);
                    }
                }
            }
        }

        // Calculate delivery cost
        $deliveryCost = $this->calculateDeliveryCost();
        
        $total = (float) max(0, $subtotal - $voucherDiscount + $deliveryCost);

        return response()->json([
            'status' => true,
            'message' => __('website.cart_updated'),
            'count' => count($cart),
            'item_price' => number_format($updatedItemPrice, 3),
            'subtotal' => number_format($subtotal, 3),
            'discount' => number_format($voucherDiscount, 3),
            'delivery_cost' => number_format($deliveryCost, 3),
            'total' => number_format($total, 3),
            'currency' => Currency::getCurrentCurrencySign()
        ]);
    }

    public function updateNotes(Request $request)
    {
        $productId = $request->product_id;
        $notes = $request->input('notes', '');
        $cart = session()->get('cart', []);

        // Find and update the notes for the product
        foreach ($cart as &$item) {
            if ($item['product_id'] == $productId) {
                $item['notes'] = $notes;
                break;
            }
        }

        session()->put('cart', $cart);

        return response()->json([
            'status' => true,
            'message' => __('website.notes_updated')
        ]);
    }

    public function remove(Request $request)
    {
        $productId = (int) $request->product_id;
        $cart = session()->get('cart', []);
        $cart = array_filter($cart, function ($item) use ($productId) {
            return (int) ($item['product_id'] ?? 0) !== $productId;
        });
        
        // Re-index array after filtering
        $cart = array_values($cart);

        session()->put('cart', $cart);

        // Calculate updated totals
        $cartProducts = collect($cart)->map(function ($item) {
            return [
                'price' => (float) $item['price']
            ];
        });

        $subtotal = (float) $cartProducts->sum('price');

        // Recalculate voucher discount
        $appliedVoucher = session('applied_voucher');
        $voucherDiscount = 0;

        if ($appliedVoucher) {
            $voucher = Coupon::where('code', $appliedVoucher['code'])
                ->where('active', 1)
                ->first();
            if ($voucher) {
                $userId = auth('web')->id();
                $minOrderPrice = (float) $voucher->min_order_price;
                if ($voucher->isValidForUser($userId) && $subtotal >= $minOrderPrice) {
                    if ($voucher->type === 'percent') {
                        $voucherDiscount = $subtotal * ((float) $voucher->value / 100);
                    } else {
                        $voucherDiscount = min((float) $voucher->value, $subtotal);
                    }
                } else {
                    // Voucher no longer valid, remove it
                    session()->forget('applied_voucher');
                    $voucherDiscount = 0;
                }
            }
        }

        // Calculate delivery cost
        $deliveryCost = $this->calculateDeliveryCost();
        
        $total = (float) max(0, $subtotal - $voucherDiscount + $deliveryCost);

        return response()->json([
            'status' => true,
            'message' => __('website.removed_from_cart_successfully'),
            'count' => count($cart),
            'cart_empty' => count($cart) === 0,
            'subtotal' => number_format($subtotal, 3),
            'discount' => number_format($voucherDiscount, 3),
            'delivery_cost' => number_format($deliveryCost, 3),
            'total' => number_format($total, 3),
            'currency' => Currency::getCurrentCurrencySign()
        ]);
    }

    /**
     * Clear the entire cart
     */
    public function clear()
    {
        session()->forget('cart');
        session()->forget('applied_voucher');

        return response()->json([
            'status' => true,
            'message' => __('website.cart_cleared') ?? 'تم مسح السلة'
        ]);
    }

    private function getAddonsData($addonIds)
    {
        if (empty($addonIds)) {
            return [];
        }

        return \App\Models\Addon::where('active', 1)
            ->whereIn('id', $addonIds)
            ->get(['id', 'name'])
            ->toArray();
    }

    private function getSubproductsData($subproducts, $boxAddonsFallback)
    {
        // Preferred: subproducts is an array of [product_id, addons[]]
        $items = [];
        if (!empty($subproducts) && is_array($subproducts)) {
            $subProductIds = array_values(array_unique(array_map(fn($sp)=> (int)($sp['product_id'] ?? 0), $subproducts)));
            $subProducts = Product::whereIn('id', $subProductIds)->get(['id','name']);
            $subIdToName = $subProducts->pluck('name','id');
            foreach ($subproducts as $sp) {
                $pid = (int) ($sp['product_id'] ?? 0);
                if ($pid <= 0) continue;
                $addonIds = array_map('intval', (array) ($sp['addons'] ?? []));
                $addons = empty($addonIds) ? collect() : \App\Models\Addon::where('active', 1)->whereIn('id', $addonIds)->get(['id','name']);
                $items[] = [
                    'sub_product_id' => $pid,
                    'sub_product_name' => (string) ($subIdToName[$pid] ?? ''),
                    'addons' => $addons->toArray(),
                ];
            }
            return $items;
        }
        // Fallback: legacy box_addons map
        if (!empty($boxAddonsFallback) && is_array($boxAddonsFallback)) {
            $subProductIds = array_keys($boxAddonsFallback);
            $subProducts = Product::whereIn('id', $subProductIds)->get(['id','name']);
            $subIdToName = $subProducts->pluck('name', 'id');
            foreach ($boxAddonsFallback as $subProductId => $addonIds) {
                if (!is_array($addonIds) || empty($addonIds)) continue;
                $addons = \App\Models\Addon::where('active', 1)
                    ->whereIn('id', $addonIds)
                    ->get(['id','name']);
                $items[] = [
                    'sub_product_id' => (int) $subProductId,
                    'sub_product_name' => (string) ($subIdToName[$subProductId] ?? ''),
                    'addons' => $addons->toArray(),
                ];
            }
        }
        return $items;
    }

    private function getBoxProductsList($productIds)
    {
        if (empty($productIds) || !is_array($productIds)) return [];
        return Product::whereIn('id', $productIds)->get(['id','name'])->toArray();
    }

    private function subproductsEqual($a, $b): bool
    {
        // Normalize to arrays of ['product_id'=>int, 'addons'=>sorted array of ints]
        $norm = function ($arr) {
            if (!is_array($arr)) return [];
            return collect($arr)->map(function ($sp) {
                $pid = (int) ($sp['product_id'] ?? 0);
                $addons = collect((array) ($sp['addons'] ?? []))->map(fn($id)=> (int) $id)->sort()->values()->toArray();
                return ['product_id' => $pid, 'addons' => $addons];
            })->sortBy(fn($sp) => $sp['product_id'].'-'.implode(',', $sp['addons']))->values()->toArray();
        };
        return $norm($a) === $norm($b);
    }

    /**
     * Calculate delivery cost based on current city
     * Gets shipping_fee_near from locations table for the selected city
     * Returns 0 if order type is pickup
     */
    private function calculateDeliveryCost(): float
    {
        // Don't calculate delivery cost for pickup orders
        $menuType = session('menu_type', 'delivery');
        if ($menuType === 'pickup') {
            return 0;
        }

        $userLocation = session('user_location');
        if (!$userLocation || !isset($userLocation['city_id']) || !$userLocation['city_id']) {
            return 0;
        }

        $cityId = (int) $userLocation['city_id'];
        
        // Get shipping_fee_near from locations table for the selected city
        $city = Location::where('type', 'city')
            ->where('id', $cityId)
            ->where('active', true)
            ->select('id', 'shipping_fee_near', 'shipping_fee_far')
            ->first();

        if (!$city) {
            return 0;
        }

        // Use shipping_fee_near as delivery cost from the selected city
        $deliveryCost = (float) ($city->shipping_fee_near ?? 0);
        
        return $deliveryCost;
    }
}
