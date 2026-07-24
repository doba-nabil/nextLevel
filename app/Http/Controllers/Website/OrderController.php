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
use App\Services\BookeeyService;
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
                    'guest_email' => 'required|email|max:255',
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
                $emailExists = \App\Models\User::where('email', $email)->exists();

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

                $branch = \App\Models\Branch::where('active', 1)->with('workingHours')->find($branchId);
                if (!$branch) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', __('website.invalid_branch') ?? 'الفرع المختار غير صحيح');
                }

                // Check if branch is currently open
                if (!$branch->isCurrentlyOpen()) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', __('website.restaurant_is_closed') ?? 'المطعم مغلق حالياً ولا يمكن إتمام الطلب');
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
                // For delivery, check if any branch in the city is open
                $userLocation = session('user_location');
                if ($userLocation && isset($userLocation['city_id']) && $userLocation['city_id']) {
                    $cityId = (int) $userLocation['city_id'];
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
                            $branchId = $branch->id;
                            break;
                        }
                    }

                    if (!$hasOpenBranch && $branches->isNotEmpty()) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', __('website.restaurant_is_closed') ?? 'المطعم مغلق حالياً ولا يمكن إتمام الطلب');
                    }

                    // If no branch found but we have a city, try to get first branch
                    if (!$branchId && $branches->isNotEmpty()) {
                        $branchId = $branches->first()->id;
                    }
                }
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

            // Validate all products are available before creating order
            foreach ($cart as $item) {
                $product = Product::where('active', true)
                    ->find($item['product_id']);
                if (!$product) {
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', __('website.product_not_found') ?? 'أحد المنتجات غير موجود')
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
                            ->whereHas('cities', function($q) use ($cityId) {
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

            $order = Order::create([
                'user_id' => $isAuthenticated ? auth('web')->id() : null,
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
                'lat' => $isAuthenticated && auth('web')->user()->lat ? auth('web')->user()->lat : null,
                'long' => $isAuthenticated && auth('web')->user()->long ? auth('web')->user()->long : null,
                'status' => 'pending',
                'total' => 0,
            ]);

            $total = 0;

            foreach ($cart as $item) {
                $product = Product::where('active', true)
                    ->find($item['product_id']);
                if (!$product) continue;

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
                        if (!is_array($addonIds)) continue;
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

            DB::commit();

            return redirect()->route('website.orders.payment', $order->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function payment($orderId)
    {
        $order = Order::with('items.addons', 'items.product', 'branch')->findOrFail($orderId);

        // Check if restaurant is closed
        $restaurantIsClosed = false;
        if ($order->order_type === 'pick_up' && $order->branch) {
            $order->branch->load('workingHours');
            $restaurantIsClosed = !$order->branch->isCurrentlyOpen();
        } elseif ($order->order_type === 'delivery') {
            $cityId = $order->city_id ?? null;
            if ($cityId) {
                $branches = \App\Models\Branch::where('active', 1)
                    ->whereHas('cities', function($q) use ($cityId) {
                        $q->where('locations.id', $cityId);
                    })
                    ->with('workingHours')
                    ->get();

                $hasOpenBranch = false;
                foreach ($branches as $branch) {
                    if ($branch->isCurrentlyOpen()) {
                        $hasOpenBranch = true;
                        break;
                    }
                }

                $restaurantIsClosed = !$hasOpenBranch && $branches->isNotEmpty();
            }
        }

        if ($restaurantIsClosed) {
            return redirect()->route('website.checkout')
                ->with('error', __('website.restaurant_is_closed') ?? 'المطعم مغلق حالياً ولا يمكن إتمام الطلب');
        }

        return view('website.checkout.payment', compact('order'));
    }

    public function payment_post($orderId, Request $request)
    {
        $order = Order::with('items.addons', 'items.product', 'branch')->findOrFail($orderId);

        // Check if restaurant is closed
        $restaurantIsClosed = false;
        if ($order->order_type === 'pick_up' && $order->branch) {
            $order->branch->load('workingHours');
            $restaurantIsClosed = !$order->branch->isCurrentlyOpen();
        } elseif ($order->order_type === 'delivery') {
            $cityId = $order->city_id ?? null;
            if ($cityId) {
                $branches = \App\Models\Branch::where('active', 1)
                    ->whereHas('cities', function($q) use ($cityId) {
                        $q->where('locations.id', $cityId);
                    })
                    ->with('workingHours')
                    ->get();

                $hasOpenBranch = false;
                foreach ($branches as $branch) {
                    if ($branch->isCurrentlyOpen()) {
                        $hasOpenBranch = true;
                        break;
                    }
                }

                $restaurantIsClosed = !$hasOpenBranch && $branches->isNotEmpty();
            }
        }

        if ($restaurantIsClosed) {
            return redirect()->route('website.checkout')
                ->with('error', __('website.restaurant_is_closed') ?? 'المطعم مغلق حالياً ولا يمكن إتمام الطلب');
        }

        $paymentMethod = $request->input('payment_method');
        $payType = $request->input('pay_type', 'knet'); // Default to knet

        if (!$paymentMethod) {
            return back()->with('error', __('website.please_select_payment_method'));
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

                    $this->createBoxChildItems($order);

                    // Add points to user after successful payment
                    $this->addPointsToUser($order);

                    // Send notification to all admins
                    $this->sendOrderNotification($order);

                    DB::commit();

                    session()->forget(['cart', 'applied_voucher']);

                    // Send Firebase notification after successful payment
                    $firebaseSent = false;
                    try {
                        $order->load('branch');
                        if ($order->branch && $order->branch->firebase) {
                            $lang = $order->branch->lang ?? 'ar';
                            $firebaseService = new FirebaseNotificationService();
                            $result = $firebaseService->sendNewOrderNotification($order, $lang);
                            $firebaseSent = $result['success'] ?? false;
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to send Firebase notification for new order (wallet payment)', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                        $firebaseSent = false;
                    }

                    // Send to Armada for delivery orders
                    if ($order->order_type === 'delivery') {
                        try {
                            $order->load('branch');
                            if ($order->branch && !empty($order->branch->armada_key)) {
                                \Log::info('Sending order to Armada (wallet payment)', [
                                    'order_id' => $order->id,
                                    'branch_id' => $order->branch_id,
                                ]);
                                $this->armadaService->createOrder($order);
                            }
                        } catch (\Exception $e) {
                            \Log::error('Failed to create Armada Order (wallet payment)', [
                                'order_id' => $order->id,
                                'branch_id' => $order->branch_id,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
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

                    // Armada delivery integration disabled - will be triggered manually if needed
                    // if ($order->order_type === 'delivery') {
                    //     try {
                    //         $order->load('branch');
                    //         \Log::info('Attempting to create Armada order (wallet payment)', [
                    //             'order_id' => $order->id,
                    //             'branch_id' => $order->branch_id,
                    //             'has_armada_key' => !empty($order->branch->armada_key ?? null)
                    //         ]);
                    //         $this->armadaService->createOrder($order);
                    //     } catch (\Exception $e) {
                    //         \Log::error('Failed to create Armada Order (wallet payment)', [
                    //             'order_id' => $order->id,
                    //             'branch_id' => $order->branch_id,
                    //             'error' => $e->getMessage(),
                    //             'trace' => $e->getTraceAsString()
                    //         ]);
                    //     }
                    // }

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


                    return $this->processBookeeyPayment($order, $gatewayAmount, $payType);

                case 'knet':
                    $gatewayAmount = $orderTotal;

                    $order->update([
                        'payment_method' => 'knet',
                        'wallet_amount' => 0,
                        'gateway_amount' => $gatewayAmount,
                        'payment_status' => 'pending'
                    ]);

                    DB::commit();

                    return $this->processBookeeyPayment($order, $gatewayAmount, 'knet');

                case 'credit':
                    $gatewayAmount = $orderTotal;

                    $order->update([
                        'payment_method' => 'credit',
                        'wallet_amount' => 0,
                        'gateway_amount' => $gatewayAmount,
                        'payment_status' => 'pending'
                    ]);

                    DB::commit();

                    return $this->processBookeeyPayment($order, $gatewayAmount, 'credit');

                case 'amex':
                    $gatewayAmount = $orderTotal;

                    $order->update([
                        'payment_method' => 'amex',
                        'wallet_amount' => 0,
                        'gateway_amount' => $gatewayAmount,
                        'payment_status' => 'pending'
                    ]);

                    DB::commit();

                    return $this->processBookeeyPayment($order, $gatewayAmount, 'amex');

                case 'applepay':
                    $gatewayAmount = $orderTotal;

                    $order->update([
                        'payment_method' => 'applepay',
                        'wallet_amount' => 0,
                        'gateway_amount' => $gatewayAmount,
                        'payment_status' => 'pending'
                    ]);

                    DB::commit();

                    return $this->processBookeeyPayment($order, $gatewayAmount, 'applepay');

                case 'cash':
                    // Check if cash on delivery is enabled
                    if (\App\Models\Setting::getValue('enable_cash_on_delivery') != '1') {
                        return back()->with('error', __('website.invalid_payment_method'));
                    }

                    $order->update([
                        'payment_method' => 'cash',
                        'wallet_amount' => 0,
                        'gateway_amount' => 0,
                        'payment_status' => 'pending', // Payment upon delivery
                        'status' => 'processing'
                    ]);

                    $this->createBoxChildItems($order);

                    // Add points to user
                    $this->addPointsToUser($order);

                    // Send notification to all admins
                    $this->sendOrderNotification($order);

                    DB::commit();

                    session()->forget(['cart', 'applied_voucher']);
                    
                    // Send Firebase notification
                    try {
                        $order->load('branch');
                        if ($order->branch && $order->branch->firebase) {
                            $lang = $order->branch->lang ?? 'ar';
                            $firebaseService = new \App\Services\FirebaseNotificationService();
                            $firebaseService->sendNewOrderNotification($order, $lang);
                        }
                    } catch (\Exception $e) {
                         \Log::error('Failed to send Firebase notification for new order (cash payment)', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                    
                    // Send to Armada for delivery orders
                    if ($order->order_type === 'delivery') {
                        try {
                            $order->load('branch');
                            if ($order->branch && !empty($order->branch->armada_key)) {
                                \Log::info('Sending order to Armada (cash payment)', [
                                    'order_id' => $order->id,
                                    'branch_id' => $order->branch_id,
                                ]);
                                $this->armadaService->createOrder($order);
                            }
                        } catch (\Exception $e) {
                            \Log::error('Failed to create Armada Order (cash payment)', [
                                'order_id' => $order->id,
                                'branch_id' => $order->branch_id,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    }

                    // Send WhatsApp confirmation
                    try {
                        $order->load('user');
                        $whatsappService = new \App\Services\WhatsAppService();
                        $whatsappService->sendOrderConfirmation($order);
                    } catch (\Exception $e) {
                        \Log::error('Failed to send WhatsApp confirmation for new order (cash payment)', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    return redirect()->route('website.orders.success', $order->id)
                        ->with('success', __('website.order_placed_successfully'));

                case 'bookeey':
                    $gatewayAmount = $orderTotal;

                    $order->update([
                        'payment_method' => 'bookeey',
                        'wallet_amount' => 0,
                        'gateway_amount' => $gatewayAmount,
                        'payment_status' => 'pending'
                    ]);

                    DB::commit();

                    return $this->processBookeeyPayment($order, $gatewayAmount, $payType);

                case 'myfatoorah': // Fallback or legacy support if needed
                    $gatewayAmount = $orderTotal;

                    $order->update([
                        'payment_method' => 'bookeey', // Migrate to bookeey
                        'wallet_amount' => 0,
                        'gateway_amount' => $gatewayAmount,
                        'payment_status' => 'pending'
                    ]);

                    DB::commit();

                    return $this->processBookeeyPayment($order, $gatewayAmount, $payType);

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

    private function processBookeeyPayment($order, $amount, $payType = 'knet')
    {
        try {
            $bookeey = new BookeeyService();

            $phone = $order->user ? $order->user->phone : $order->guest_phone;
            $phone = preg_replace('/[^0-9]/', '', $phone);

            $amount = number_format((float)$amount, 3, '.', '');

            $invoiceData = [
                'CustomerName'       => $order->user ? $order->user->name : $order->guest_name,
                'InvoiceValue'       => $amount,
                'CustomerEmail'      => $order->user ? $order->user->email : $order->guest_email,
                'CallBackUrl'        => route('website.orders.payment.callback', $order->id),
                'ErrorUrl'           => route('website.orders.payment.failed', $order->id),
                'CustomerMobile'     => $phone,
                'CustomerReference'  => $order->order_number,
                'pay_type'           => $payType, // Pass pay_type to Bookeey service
                // 'UserDefinedField'   => 'Order-' . $order->id, // Add if supported
            ];

            $payment = $bookeey->createInvoice($invoiceData);

            if ($payment['success']) {
                // Save trackId or paymentId for status checking
                $paymentId = $payment['trackId'] ?? $payment['paymentId'] ?? $payment['invoiceId'] ?? null;
                if ($paymentId) {
                    $order->update(['payment_id' => $paymentId]);
                }

                return redirect($payment['invoiceURL']);
            }

            throw new \Exception($payment['error'] ?? 'Invoice creation failed');

        } catch (\Exception $e) {
            // Log the full error
            \Log::error('Bookeey Payment Error', [
                'order_id' => $order->id,
                'amount' => $amount,
                'payment_method' => $order->payment_method,
                'error' => $e->getMessage()
            ]);

            // Save failed payment status in database
            $order->update([
                'payment_status' => 'failed',
                'payment_response' => json_encode([
                    'error' => $e->getMessage(),
                    'timestamp' => now(),
                    'note' => 'Bookeey payment initiation failed'
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
            $subProducts = $product->products()->get();
            $addonsBySubId = [];
            foreach ($subproducts as $sp) {
                $pid = (int) ($sp['product_id'] ?? 0);
                $addonsBySubId[$pid] = array_map('intval', (array) ($sp['addons'] ?? []));
            }
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
            $bookeey = new BookeeyService();
            // Bookeey uses order_number (MerchantTxnRefNo) for status check
            // TrackId may come in callback but we use order_number for status check
            $trackId = $request->input('TrackId') ?? $request->input('trackId') ?? $request->input('paymentId');
            if ($trackId) {
                $order->update(['payment_id' => $trackId]);
            }

            // Use order_number for status check (MerchantTxnRefNo)
            $payment = $bookeey->getPaymentStatus($order->order_number);

            // Log payment status for debugging
            \Log::info('Payment Callback - Payment Status Check', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_success' => $payment['success'] ?? false,
                'payment_isPaid' => $payment['isPaid'] ?? false,
                'payment_status' => $payment['status'] ?? 'unknown',
                'payment_data' => $payment['paymentStatus'] ?? null
            ]);

            $order->update([
                'payment_response' => json_encode($payment['data'] ?? [])
            ]);

            if ($payment['success'] && (($payment['isPaid'] ?? false) || $payment['status'] == 'Paid' || $payment['status'] == 'Captured')) {
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

                $this->createBoxChildItems($order);

                // Add points to user after successful payment
                $this->addPointsToUser($order);

                // Send notification to all admins
                $this->sendOrderNotification($order);

                session()->forget(['cart', 'applied_voucher']);

                // Send Firebase notification after successful payment
                $firebaseSent = false;
                try {
                    $order->load('branch');
                    if ($order->branch && $order->branch->firebase) {
                        $lang = $order->branch->lang ?? 'ar';
                        $firebaseService = new FirebaseNotificationService();
                        $result = $firebaseService->sendNewOrderNotification($order, $lang);
                        $firebaseSent = $result['success'] ?? false;
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send Firebase notification for new order (electronic payment)', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                    $firebaseSent = false;
                }

                // Send to Armada for delivery orders
                if ($order->order_type === 'delivery') {
                    try {
                        $order->load('branch');
                        if ($order->branch && !empty($order->branch->armada_key)) {
                            \Log::info('Sending order to Armada (electronic payment)', [
                                'order_id' => $order->id,
                                'branch_id' => $order->branch_id,
                            ]);
                            $this->armadaService->createOrder($order);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to create Armada Order (electronic payment)', [
                            'order_id' => $order->id,
                            'branch_id' => $order->branch_id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
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

                // Armada delivery integration disabled - will be triggered manually if needed
                // if ($order->order_type === 'delivery') {
                //      try {
                //         $order->load('branch');
                //         \Log::info('Attempting to create Armada order', [
                //             'order_id' => $order->id,
                //             'branch_id' => $order->branch_id,
                //             'has_armada_key' => !empty($order->branch->armada_key ?? null)
                //         ]);
                //         $this->armadaService->createOrder($order);
                //      } catch (\Exception $e) {
                //          \Log::error('Failed to create Armada Order', [
                //              'order_id' => $order->id,
                //              'branch_id' => $order->branch_id,
                //              'error' => $e->getMessage(),
                //              'trace' => $e->getTraceAsString()
                //          ]);
                //      }
                // }

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

    /**
     * Add points to user after successful order payment
     */
    private function addPointsToUser(Order $order): void
    {
        try {
            // Only add points if order is paid and user exists
            if ($order->payment_status !== 'paid' || !$order->user) {
                return;
            }

            // Get points per order value setting
            $pointsPerOrderValue = (float) \App\Models\Setting::getValue('points_per_order_value', null, 10);

            // If points per order value is 0 or less, don't add points
            if ($pointsPerOrderValue <= 0) {
                return;
            }

            // Calculate points based on order total
            $orderTotal = (float) $order->total;
            $pointsToAdd = $orderTotal * $pointsPerOrderValue;

            // Add points to user
            $user = $order->user;
            $user->points = ($user->points ?? 0) + $pointsToAdd;
            $user->save();

            // Log points addition
            \Log::info('Points added to user after order payment', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'order_total' => $orderTotal,
                'points_per_order_value' => $pointsPerOrderValue,
                'points_added' => $pointsToAdd,
                'total_points' => $user->points
            ]);

        } catch (\Exception $e) {
            // Log error but don't fail the order
            \Log::error('Failed to add points to user after order payment', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notification to all admins about new order
     */
    private function sendOrderNotification(Order $order): void
    {
        try {
            // Get all admin users
            $admins = \App\Models\User::where('is_admin', 1)->get();

            foreach ($admins as $admin) {
                \App\Models\AdminNotification::create([
                    'admin_id' => $admin->id,
                    'order_id' => $order->id,
                    'type' => 'order',
                    'title' => __('admin.new_order_notification_title', ['order_number' => $order->order_number]),
                    'message' => __('admin.new_order_notification_message', [
                        'order_number' => $order->order_number,
                        'total' => number_format($order->total, 3),
                        'customer' => $order->user ? $order->user->name : $order->guest_name
                    ]),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send order notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
