<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAddon;
use App\Models\Product;
use App\Models\Addon;
use App\Models\Coupon;
use App\Services\MyFatoorahService;
use App\Services\FirebaseNotificationService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ArmadaDeliveryService;

class OrderController extends Controller
{
    protected $armadaService;

    public function __construct(ArmadaDeliveryService $armadaService)
    {
        $this->armadaService = $armadaService;
    }

    public function store(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->back()->with('error', __('website.cart_empty'));
        }

        DB::beginTransaction();

        try {
            $isAuthenticated = auth('web')->check();

            if (!$isAuthenticated) {
                if (!session('checkout_otp_verified')) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', __('website.please_verify_phone_and_create_account'));
                }

                $request->validate([
                    'guest_name' => 'required|string|max:255',
                    'guest_phone' => 'required|string|max:20',
                    'guest_email' => 'nullable|email|max:255',
                    'address' => 'required|string',
                ]);

                $phone = $request->input('guest_phone');
                $email = $request->input('guest_email');

                $phone = preg_replace('/[^0-9]/', '', $phone);
                if (str_starts_with($phone, '0')) {
                    $phone = '965' . substr($phone, 1);
                } elseif (!str_starts_with($phone, '965')) {
                    $phone = '965' . $phone;
                }

                $phoneExists = \App\Models\User::where('phone', $phone)->exists();
                $emailExists = $email ? \App\Models\User::where('email', $email)->exists() : false;

                if ($phoneExists || $emailExists) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors([
                            'guest_phone' => $phoneExists ? __('website.phone_already_registered') : null,
                            'guest_email' => $emailExists ? __('website.email_already_registered') : null,
                        ])
                        ->with('error', __('website.phone_or_email_exists'));
                }
            }

            $orderType = $request->order_type ?? session('menu_type', 'delivery');
            if ($orderType === 'pickup') {
                $orderType = 'pick_up';
            }
            $isPickup = $orderType === 'pick_up';

            if ($isPickup) {
                if (!$request->branch_id && !session('pickup_branch_id')) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', __('website.please_select_branch_first') ?? 'من فضلك اختر الفرع أولًا');
                }

                $branchId = $request->branch_id ?? session('pickup_branch_id');

                $branch = \App\Models\Branch::where('active', 1)->find($branchId);
                if (!$branch) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', __('website.invalid_branch') ?? 'الفرع المختار غير صحيح');
                }

                if ($request->meal_type === 'scheduled') {
                    if (!$request->scheduled_date || !$request->scheduled_time) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', __('website.please_select_pickup_date_and_time') ?? 'من فضلك اختر تاريخ ووقت الاستلام');
                    }

                    $scheduledDateTime = \Carbon\Carbon::parse($request->scheduled_date . ' ' . $request->scheduled_time);
                    if ($scheduledDateTime->isPast()) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', __('website.scheduled_date_cannot_be_in_past') ?? 'لا يمكن اختيار تاريخ ووقت في الماضي');
                    }
                }
            }

            $appliedVoucher = session('applied_voucher');
            $voucherDiscount = 0;
            $couponId = null;
            $couponCode = null;

            $branchId = null;
            if ($isPickup) {
                $branchId = $request->branch_id ?? session('pickup_branch_id');
            } else {
                $userLocation = session('user_location');
                if ($userLocation && isset($userLocation['city_id']) && $userLocation['city_id']) {
                    $cityId = (int) $userLocation['city_id'];
                    $branch = \App\Models\Branch::where('active', 1)
                        ->whereHas('cities', function ($q) use ($cityId) {
                            $q->where('locations.id', $cityId);
                        })
                        ->first();

                    if ($branch) {
                        $branchId = $branch->id;
                    }
                }
            }

            if ($branch && !$branch->isOpen()) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', __('website.branch_closed') ?? 'الفرع مغلق حالياً');
            }

            $scheduledDate = null;

            $scheduledTime = null;

            if ($isPickup && $request->meal_type === 'scheduled') {
                $scheduledDateInput = $request->input('scheduled_date');
                $scheduledTimeInput = $request->input('scheduled_time');

                if (!empty($scheduledDateInput)) {
                    $scheduledDate = $scheduledDateInput;
                }
                if (!empty($scheduledTimeInput)) {
                    $scheduledTime = $scheduledTimeInput;
                }
            }

            $branchStockService = app(\App\Services\BranchStockService::class);

            // Validate all products are available before creating order
            foreach ($cart as $item) {
                $product = Product::withoutGlobalScope('city_availability')->where('active', true)
                    ->find($item['product_id']);
                if (!$product) {
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', __('website.product_not_found') ?? 'أحد المنتجات غير موجود')
                        ->withInput();
                }

                $stockError = $branchStockService->validateCartQuantity(
                    $product,
                    (int) ($item['quantity'] ?? 1),
                    $branchId
                );
                if ($stockError) {
                    DB::rollBack();
                    $productName = $product->getTranslation('name', app()->getLocale())
                        ?: ($product->name['ar'] ?? '');
                    return redirect()->back()
                        ->with('error', $productName ? "{$productName}: {$stockError}" : $stockError)
                        ->withInput();
                }

                // Check product availability based on order type
                if ($isPickup && $branchId) {
                    $isAvailable = $product->branches()
                        ->where('branches.id', $branchId)
                        ->where('branches.active', true)
                        ->where('product_branches.status', 'available')
                        ->exists();

                    if (!$isAvailable) {
                        DB::rollBack();
                        return redirect()->back()
                            ->with('error', __('website.product_not_available_in_branch') ?? 'المنتج "' . ($product->name[app()->getLocale()] ?? $product->name['ar'] ?? '') . '" غير متاح في الفرع المختار حالياً')
                            ->withInput();
                    }
                } else {
                    $userLocation = session('user_location');
                    if ($userLocation && isset($userLocation['city_id']) && $userLocation['city_id']) {
                        $cityId = (int) $userLocation['city_id'];
                        $isAvailable = $product->branches()
                            ->whereHas('cities', function ($q) use ($cityId) {
                                $q->where('locations.id', $cityId);
                            })
                            ->where('branches.active', true)
                            ->where('product_branches.status', 'available')
                            ->exists();

                        if (!$isAvailable) {
                            DB::rollBack();
                            return redirect()->back()
                                ->with('error', __('website.product_not_available_in_selected_city') ?? 'المنتج "' . ($product->name[app()->getLocale()] ?? $product->name['ar'] ?? '') . '" غير متاح في المدينة المختارة حالياً')
                                ->withInput();
                        }
                    }
                }
            }

            // Minimum Order Validation for Delivery
            if ($orderType === 'delivery') {
                $userLocation = session('user_location');
                if ($userLocation && isset($userLocation['city_id']) && $userLocation['city_id']) {
                    $cityId = (int) $userLocation['city_id'];
                    $city = \App\Models\Location::where('active', true)->find($cityId);
                    
                    if ($city && $city->min_order_near > 0) {
                        $cartSubtotal = 0;
                        foreach ($cart as $item) {
                            $cartSubtotal += (float) ($item['price'] ?? 0);
                        }
                        
                        if ($cartSubtotal < $city->min_order_near) {
                            DB::rollBack();
                            return redirect()->back()
                                ->withInput()
                                ->with('error', __('website.min_order_error', ['amount' => number_format($city->min_order_near, 3)]));
                        }
                    }
                }
            }

            $addressId = ($isAuthenticated && $orderType === 'delivery') ? $request->address_id : null;
            $orderLat = null;
            $orderLong = null;

            if ($addressId) {
                $address = \App\Models\Address::find($addressId);
                if ($address) {
                    $orderLat = $address->latitude;
                    $orderLong = $address->longitude;
                }
            }

            $order = Order::create([
                'user_id' => $isAuthenticated ? auth('web')->id() : null,
                'address_id' => $addressId,
                'guest_name' => !$isAuthenticated ? $request->guest_name : null,
                'guest_phone' => !$isAuthenticated ? $request->guest_phone : null,
                'guest_email' => !$isAuthenticated ? $request->guest_email : null,
                'guest_address' => !$isAuthenticated ? $request->address : null,
                'order_notes' => $request->input('order_notes'),
                'meal_type' => $request->meal_type,
                'order_type' => $orderType,
                'branch_id' => $branchId,
                'scheduled_date' => $scheduledDate,
                'scheduled_time' => $scheduledTime,
                'lat' => $orderLat,
                'long' => $orderLong,
                'status' => 'pending',
                'total' => 0,
            ]);

            $total = 0;

            foreach ($cart as $item) {
                $product = Product::withoutGlobalScope('city_availability')->where('active', true)
                    ->find($item['product_id']);
                if (!$product)
                    continue;

                $itemPrice = (float) $item['price'];

                $itemNotes = $request->input('item_notes.' . $item['product_id'], $item['notes'] ?? '');

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => (int) $item['quantity'],
                    'price' => (float) $product->getCurrentPrice(session('currency')),
                    'total' => $itemPrice,
                    'notes' => $itemNotes,
                    'meta' => !empty($item['is_box']) ? [
                        'is_box' => true,
                        'subproducts' => $item['subproducts'] ?? [],
                    ] : null,
                ]);

                if (!empty($item['addons'])) {
                    foreach ($item['addons'] as $addonId) {
                        $addon = Addon::where('active', 1)->find($addonId);
                        if ($addon) {
                            OrderItemAddon::create([
                                'order_item_id' => $orderItem->id,
                                'addon_id' => $addon->id,
                                'name' => $addon->name,
                                'price' => (float) $addon->getCurrentPrice(session('currency')),
                            ]);
                        }
                    }
                }

                if (!empty($item['is_box']) && !empty($item['box_addons']) && is_array($item['box_addons'])) {
                    foreach ($item['box_addons'] as $subProductId => $addonIds) {
                        if (!is_array($addonIds))
                            continue;
                        foreach ($addonIds as $addonId) {
                            $addon = Addon::where('active', 1)->find($addonId);
                            if ($addon) {
                                OrderItemAddon::create([
                                    'order_item_id' => $orderItem->id,
                                    'addon_id' => $addon->id,
                                    'name' => $addon->name,
                                    'price' => (float) $addon->getCurrentPrice(session('currency')),
                                ]);
                            }
                        }
                    }
                }

                $total += $itemPrice;
            }

            if ($appliedVoucher) {
                $voucher = Coupon::where('code', $appliedVoucher['code'])
                    ->where('active', 1)
                    ->first();

                if ($voucher) {
                    $userId = $isAuthenticated ? auth('web')->id() : null;
                    $minOrderPrice = (float) $voucher->min_order_price;

                    if ($voucher->isValidForUser($userId) && $total >= $minOrderPrice) {
                        if ($voucher->type === 'percent') {
                            $voucherDiscount = (float) ($total * ((float) $voucher->value / 100));
                        } else {
                            $voucherDiscount = (float) min((float) $voucher->value, $total);
                        }

                        $couponId = $voucher->id;
                        $couponCode = $voucher->code;

                        $voucher->decreaseUsage();
                    }
                }
            }

            $deliveryCost = $this->calculateDeliveryCost();

            $finalTotal = (float) max(0, $total - $voucherDiscount + $deliveryCost);

            $order->update([
                'total' => $finalTotal,
                'delivery_cost' => $deliveryCost,
                'coupon_id' => $couponId,
                'coupon_code' => $couponCode,
                'discount_amount' => $voucherDiscount,
            ]);

            event(new \App\Events\NewOrder($order));

            DB::commit();

            return redirect()->route('website.orders.payment', $order->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function payment($orderId)
    {
        $order = Order::with('items.addons', 'items.product')->findOrFail($orderId);

        $stockError = app(\App\Services\BranchStockService::class)->validateOrderItems($order);
        if ($stockError) {
            return redirect()->route('cart.index')->with('error', $stockError);
        }

        $subProductIds = [];
        $addonIds = [];
        foreach ($order->items as $item) {
            if ($item->meta && isset($item->meta['subproducts'])) {
                foreach ($item->meta['subproducts'] as $sp) {
                    if (isset($sp['product_id'])) {
                        $subProductIds[] = $sp['product_id'];
                    }
                    if (isset($sp['addons']) && is_array($sp['addons'])) {
                        $addonIds = array_merge($addonIds, $sp['addons']);
                    }
                }
            }
        }
        
        $subProducts = [];
        if (!empty($subProductIds)) {
            $subProducts = Product::withoutGlobalScope('city_availability')
                ->whereIn('id', $subProductIds)
                ->pluck('name', 'id')
                ->toArray();
        }
        
        $subAddons = [];
        if (!empty($addonIds)) {
            $subAddons = Addon::where('active', 1)
                ->whereIn('id', $addonIds)
                ->pluck('name', 'id')
                ->toArray();
        }

        return view('website.checkout.payment', compact('order', 'subProducts', 'subAddons'));
    }

    public function payment_post($orderId, Request $request)
    {
        $order = Order::with('items.addons', 'items.product')->findOrFail($orderId);
        $paymentMethod = $request->input('payment_method');

        if (!$paymentMethod) {
            return back()->with('error', __('website.please_select_payment_method'));
        }

        $stockError = app(\App\Services\BranchStockService::class)->validateOrderItems($order);
        if ($stockError) {
            return back()->with('error', $stockError);
        }

        try {
            DB::beginTransaction();

            $orderTotal = (float) $order->total;
            $walletAmount = 0;
            $gatewayAmount = 0;

            $user = auth('web')->user();
            $walletBalance = 0;
            if ($user && $user->wallet) {
                $walletBalance = (float) $user->wallet->balance;
            }

            switch ($paymentMethod) {
                case 'wallet':
                    if ($walletBalance < $orderTotal) {
                        return back()->with('error', __('website.insufficient_wallet_balance'));
                    }

                    $walletAmount = $orderTotal;

                    $user->wallet->withdraw($walletAmount, [
                        'description' => 'Payment for order #' . $order->order_number
                    ]);

                    $order->update([
                        'payment_method' => 'wallet',
                        'wallet_amount' => $walletAmount,
                        'gateway_amount' => 0,
                        'payment_status' => 'paid',
                        'status' => 'processing'
                    ]);

                    $stockError = app(\App\Services\BranchStockService::class)->validateOrderItems($order);
                    if ($stockError) {
                        throw new \RuntimeException($stockError);
                    }

                    $order->deductStock();

                    $this->createBoxChildItems($order);

                    // Add points to user after successful payment
                    $this->addPointsToUser($order);

                    // Send SMS notification to admin
                    $this->sendOrderSmsToAdmin($order);

                    DB::commit();

                    session()->forget(['cart', 'applied_voucher']);

                    // Send Firebase notification after successful payment
                    try {
                        $order->load('branch');
                        if ($order->branch && $order->branch->firebase) {
                            $lang = $order->branch->lang ?? 'ar';
                            try {
                                $firebaseService = new FirebaseNotificationService();
                                $firebaseService->sendNewOrderNotification($order, $lang);
                            } catch (\ParseError $e) {
                                \Log::error('Firebase Parse Error - PHP version incompatibility', [
                                    'order_id' => $order->id,
                                    'error' => $e->getMessage(),
                                    'php_version' => PHP_VERSION,
                                    'message' => 'Firebase requires PHP 8.3+. Notification skipped.'
                                ]);
                            } catch (\Exception $e) {
                                \Log::error('Failed to send Firebase notification for new order (wallet payment)', [
                                    'order_id' => $order->id,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to initialize Firebase notification service', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    // Send WhatsApp confirmation message
                    try {
                        $order->load('user');
                        $whatsappService = new WhatsAppService();
                        $whatsappService->sendOrderConfirmation($order);
                    } catch (\Exception $e) {
                        \Log::error('Failed to send WhatsApp confirmation for new order (wallet payment)', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    // Trigger Armada for delivery orders
                    if ($order->order_type === 'delivery') {
                        try {
                            $order->load('branch');
                            \Log::info('Attempting to create Armada order (wallet payment)', [
                                'order_id' => $order->id,
                                'branch_id' => $order->branch_id,
                                'has_armada_key' => !empty($order->branch->armada_key ?? null)
                            ]);
                            $this->armadaService->createOrder($order);
                        } catch (\Exception $e) {
                            \Log::error('Failed to create Armada Order (wallet payment)', [
                                'order_id' => $order->id,
                                'branch_id' => $order->branch_id,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    }

                    return redirect()->route('website.orders.success', $order->id)
                        ->with('success', __('website.payment_successful'));

                case 'mixed':
                    if ($walletBalance <= 0) {
                        return back()->with('error', __('website.wallet_empty'));
                    }

                    $walletAmount = min($walletBalance, $orderTotal);
                    $gatewayAmount = $orderTotal - $walletAmount;


                    $order->update([
                        'payment_method' => 'mixed',
                        'wallet_amount' => $walletAmount,
                        'gateway_amount' => $gatewayAmount,
                        'payment_status' => 'pending'
                    ]);

                    DB::commit();


                    return $this->processMyFatoorahPayment($order, $gatewayAmount);

                case 'myfatoorah':
                    $gatewayAmount = $orderTotal;

                    $order->update([
                        'payment_method' => 'myfatoorah',
                        'wallet_amount' => 0,
                        'gateway_amount' => $gatewayAmount,
                        'payment_status' => 'pending'
                    ]);

                    DB::commit();

                    return $this->processMyFatoorahPayment($order, $gatewayAmount);

                default:
                    return back()->with('error', __('website.invalid_payment_method'));
            }

        } catch (\Exception $e) {
            DB::rollBack();


            if (isset($order)) {
                $order->update([
                    'payment_status' => 'failed',
                    'payment_response' => json_encode(['error' => $e->getMessage()])
                ]);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    private function processMyFatoorahPayment($order, $amount)
    {
        try {
            $myFatoorah = new MyFatoorahService();

            $phone = $order->user ? $order->user->phone : $order->guest_phone;
            $phone = preg_replace('/[^0-9]/', '', $phone);

            $amount = number_format((float) $amount, 3, '.', '');

            $invoiceData = [
                'CustomerName' => $order->user ? $order->user->name : $order->guest_name,
                'InvoiceValue' => $amount,
                'DisplayCurrencyIso' => config('myfatoorah.currency', 'KWD'),
                'CustomerEmail' => $order->user ? $order->user->email : $order->guest_email,
                'CallBackUrl' => route('website.orders.payment.callback', $order->id),
                'ErrorUrl' => route('website.orders.payment.failed', $order->id),
                'MobileCountryCode' => '+965',
                'CustomerMobile' => $phone,
                'Language' => 'en',
                'CustomerReference' => $order->order_number,
                'UserDefinedField' => 'Order-' . $order->id,
                'NotificationOption' => 'Lnk',
            ];

            $payment = $myFatoorah->createInvoice($invoiceData);

            if ($payment['success']) {
                $order->update(['payment_id' => $payment['invoiceId']]);

                return redirect($payment['invoiceURL']);
            }

            throw new \Exception('Invoice creation failed');

        } catch (\Exception $e) {
            // Log the full error
            \Log::error('MyFatoorah Payment Error', [
                'order_id' => $order->id,
                'amount' => $amount,
                'payment_method' => $order->payment_method,
                'error' => $e->getMessage()
            ]);

            // Save failed payment status in database
            // Note: For mixed payments, wallet was NOT deducted yet, so no refund needed
            $order->update([
                'payment_status' => 'failed',
                'payment_response' => json_encode([
                    'error' => $e->getMessage(),
                    'timestamp' => now(),
                    'note' => 'Wallet not deducted - payment failed before completion'
                ])
            ]);

            return redirect()->route('website.orders.payment.failed', $order->id)
                ->with('error', __('website.payment_initiation_failed') . ': ' . $e->getMessage());
        }
    }

    private function createBoxChildItems(Order $order): void
    {
        $order->loadMissing('items.product', 'items.addons');
        foreach ($order->items as $parentItem) {
            $product = $parentItem->product;
            if (!$product || !$product->is_box) {
                continue;
            }
            if ($parentItem->children()->exists()) {
                continue;
            }
            $meta = $parentItem->meta ?? [];
            $subproducts = $meta['subproducts'] ?? [];
            $addonsBySubId = [];
            foreach ($subproducts as $sp) {
                $pid = (int) ($sp['product_id'] ?? 0);
                $addonsBySubId[$pid] = array_map('intval', (array) ($sp['addons'] ?? []));
            }
            
            $selectedProductIds = array_keys($addonsBySubId);
            if (empty($selectedProductIds)) {
                continue;
            }
            
            $subProducts = Product::withoutGlobalScope('city_availability')->whereIn('id', $selectedProductIds)->get();
            
            foreach ($subProducts as $subProduct) {
                $child = OrderItem::create([
                    'order_id' => $order->id,
                    'parent_item_id' => $parentItem->id,
                    'product_id' => $subProduct->id,
                    'quantity' => (int) $parentItem->quantity,
                    'price' => 0,
                    'total' => 0,
                ]);

                $addonIds = (array) ($addonsBySubId[$subProduct->id] ?? []);
                if (!empty($addonIds)) {
                    $addons = Addon::where('active', 1)->whereIn('id', $addonIds)->get();
                    foreach ($addons as $addon) {
                        OrderItemAddon::create([
                            'order_item_id' => $child->id,
                            'addon_id' => $addon->id,
                            'name' => $addon->name,
                            'price' => (float) $addon->getCurrentPrice(session('currency')),
                        ]);
                    }
                }
            }
        }
    }

    public function paymentCallback($orderId, Request $request)
    {
        $order = Order::findOrFail($orderId);

        try {
            $myFatoorah = new MyFatoorahService();
            $paymentId = $request->input('paymentId');

            $payment = $myFatoorah->getPaymentStatus($paymentId);

            $order->update([
                'payment_response' => json_encode($payment['data'])
            ]);

            if ($payment['success'] && $payment['status'] == 'Paid') {
                if ($order->payment_method === 'mixed' && $order->wallet_amount > 0) {
                    $user = $order->user;
                    if ($user && $user->wallet) {
                        try {
                            $user->wallet->withdraw($order->wallet_amount, [
                                'description' => 'Partial payment for order #' . $order->order_number
                            ]);
                        } catch (\Exception $e) {
                            \Log::error('Wallet Deduction Error After Payment', [
                                'order_id' => $order->id,
                                'wallet_amount' => $order->wallet_amount,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }

                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'processing'
                ]);

                $stockError = app(\App\Services\BranchStockService::class)->validateOrderItems($order);
                if ($stockError) {
                    return redirect()->route('website.checkout.payment', $order->id)
                        ->with('error', $stockError);
                }

                $order->deductStock();

                $this->createBoxChildItems($order);

                // Add points to user after successful payment
                $this->addPointsToUser($order);

                // Send SMS notification to admin
                $this->sendOrderSmsToAdmin($order);



                session()->forget(['cart', 'applied_voucher']);

                // Send Firebase notification after successful payment
                try {
                    $order->load('branch');
                    if ($order->branch && $order->branch->firebase) {
                        $lang = $order->branch->lang ?? 'ar';
                        try {
                            $firebaseService = new FirebaseNotificationService();
                            $firebaseService->sendNewOrderNotification($order, $lang);
                        } catch (\ParseError $e) {
                            \Log::error('Firebase Parse Error - PHP version incompatibility', [
                                'order_id' => $order->id,
                                'error' => $e->getMessage(),
                                'php_version' => PHP_VERSION,
                                'message' => 'Firebase requires PHP 8.3+. Notification skipped.'
                            ]);
                        } catch (\Exception $e) {
                            \Log::error('Failed to send Firebase notification for new order (electronic payment)', [
                                'order_id' => $order->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to initialize Firebase notification service', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                // Send WhatsApp confirmation message
                try {
                    $order->load('user');
                    $whatsappService = new WhatsAppService();
                    $whatsappService->sendOrderConfirmation($order);
                } catch (\Exception $e) {
                    \Log::error('Failed to send WhatsApp confirmation for new order (electronic payment)', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                // Trigger Armada for delivery orders
                if ($order->order_type === 'delivery') {
                    try {
                        $order->load('branch');
                        \Log::info('Attempting to create Armada order', [
                            'order_id' => $order->id,
                            'branch_id' => $order->branch_id,
                            'has_armada_key' => !empty($order->branch->armada_key ?? null)
                        ]);
                        $this->armadaService->createOrder($order);
                    } catch (\Exception $e) {
                        \Log::error('Failed to create Armada Order', [
                            'order_id' => $order->id,
                            'branch_id' => $order->branch_id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }

                return redirect()->route('website.orders.success', $order->id)
                    ->with('success', __('website.payment_successful'));
            } else {
                $order->update([
                    'payment_status' => 'failed',
                    'payment_response' => json_encode([
                        'data' => $payment['data'] ?? [],
                        'status' => $payment['status'] ?? 'unknown',
                        'timestamp' => now(),
                        'note' => $order->payment_method === 'mixed' ? 'Wallet not deducted - MyFatoorah payment failed' : null
                    ])
                ]);

                return redirect()->route('website.orders.payment.failed', $order->id)
                    ->with('error', __('website.payment_failed') . ' - Status: ' . ($payment['status'] ?? 'Unknown'));
            }

        } catch (\Exception $e) {
            // Log the error
            \Log::error('Payment Callback Error', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);


            $order->update([
                'payment_status' => 'failed',
                'payment_response' => json_encode([
                    'error' => $e->getMessage(),
                    'timestamp' => now(),
                    'note' => $order->payment_method === 'mixed' ? 'Wallet not deducted - payment verification failed' : null
                ])
            ]);

            return redirect()->route('website.orders.payment.failed', $order->id)
                ->with('error', __('website.payment_verification_failed') . ': ' . $e->getMessage());
        }
    }

    public function paymentFailed($orderId)
    {
        $order = Order::with('items.addons', 'items.product')->findOrFail($orderId);

        return view('website.checkout.failed', compact('order'));
    }

    public function success($orderId)
    {
        $order = Order::with('items.addons', 'items.product')->findOrFail($orderId);

        return view('website.checkout.success', compact('order'));
    }

    /**
     * Calculate delivery cost based on current city
     * Gets shipping_fee_near from locations table for the selected city
     * Returns 0 if order type is pickup
     */
    private function calculateDeliveryCost(): float
    {
        $request = request();
        $orderType = $request->order_type ?? session('menu_type', 'delivery');
        if ($orderType === 'pick_up' || $orderType === 'pickup') {
            return 0;
        }

        $userLocation = session('user_location');
        if (!$userLocation || !isset($userLocation['city_id']) || !$userLocation['city_id']) {
            return 0;
        }

        $cityId = (int) $userLocation['city_id'];

        $city = Location::where('type', 'city')
            ->where('id', $cityId)
            ->where('active', true)
            ->select('id', 'shipping_fee_near', 'shipping_fee_far')
            ->first();

        if (!$city) {
            return 0;
        }

        return (float) ($city->shipping_fee_near ?? 0);
    }

    private function addPointsToUser(Order $order): void
    {
        if ($order->user_id && $order->total > 0) {
            $pointsPerOrderValue = (float) \App\Models\Setting::getValue('points_per_order_value', null, '0');
            if ($pointsPerOrderValue > 0) {
                $points = (int) floor($order->total / $pointsPerOrderValue);
                if ($points > 0) {
                    $order->user->addPoints($points, 'earned from order #' . $order->order_number);
                }
            }
        }
    }

    private function sendOrderSmsToAdmin(Order $order): void
    {
        try {
            $smsActive = \App\Models\Setting::getValue('order_notification_sms_active', null, '0');
            $adminPhone = \App\Models\Setting::getValue('order_notification_phone', null, '');

            if ($smsActive != '1' || empty($adminPhone)) {
                return;
            }

            $order->loadMissing('items.product', 'branch');
            
            $itemsSummary = "";
            foreach ($order->items as $item) {
                $productName = $item->product->name[app()->getLocale()] ?? $item->product->name['ar'] ?? 'Product';
                $itemsSummary .= "{$productName} (x{$item->quantity}), ";
            }
            $itemsSummary = rtrim($itemsSummary, ', ');

            $branchName = $order->branch ? ($order->branch->name[app()->getLocale()] ?? $order->branch->name['ar'] ?? '') : 'N/A';
            $orderType = __('website.' . $order->order_type) ?: $order->order_type;

            $message = "New Order #{$order->order_number}\n";
            $message .= "Items: {$itemsSummary}\n";
            $message .= "Branch: {$branchName}\n";
            $message .= "Type: {$orderType}\n";
            $message .= "Total: " . number_format($order->total, 3) . " KWD";

            $smsService = new \App\Services\SmsService();
            $smsService->sendSms($adminPhone, $message);
            
            \Log::info("Admin SMS notification sent for order #{$order->order_number}");
        } catch (\Exception $e) {
            \Log::error("Failed to send admin SMS notification for order #{$order->order_number}: " . $e->getMessage());
        }
    }
}
