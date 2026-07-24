<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Branch;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function index()
    {
        // Check if cart is empty
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', __('website.cart_empty'));
        }

        // Get order type from session (normalize to 'pick_up' or 'delivery')
        $menuType = session('menu_type', 'delivery');
        $orderType = ($menuType === 'pickup') ? 'pick_up' : $menuType;
        
        // Get branches for pickup - use session data
        $branches = collect([]);
        $selectedBranch = null;
        
        if ($orderType === 'pick_up' || $menuType === 'pickup') {
            // Get selected branch from session
            $pickupBranchId = session('pickup_branch_id');
            $selectedCityId = session('pickup_city_id');
            
            // Get selected branch if exists
            if ($pickupBranchId) {
                $selectedBranch = Branch::where('active', 1)->find($pickupBranchId);
                
                // If branch exists, get all branches from the same city for change option
                if ($selectedBranch && $selectedCityId) {
                    $branches = Branch::where('active', 1)
                        ->whereHas('cities', function($q) use ($selectedCityId) {
                            $q->where('locations.id', $selectedCityId);
                        })
                        ->with('location')
                        ->orderBy('name')
                        ->get();
                }
            } else {
                // If no branch selected, get branches from user's city
                $userLocation = session('user_location');
                if ($userLocation && isset($userLocation['city_id']) && $userLocation['city_id']) {
                    $cityId = (int) $userLocation['city_id'];
                    $branches = Branch::where('active', 1)
                        ->whereHas('cities', function($q) use ($cityId) {
                            $q->where('locations.id', $cityId);
                        })
                        ->with('location')
                        ->orderBy('name')
                        ->get();
                }
            }
        }

        // Calculate cart totals
        $cartProducts = $this->getCartProducts($cart);
        $subtotal = (float) $cartProducts->sum('price');

        // Get applied voucher
        $appliedVoucher = session('applied_voucher');
        $voucherDiscount = 0;

        if ($appliedVoucher) {
            $voucherDiscount = (float) $this->calculateVoucherDiscount($appliedVoucher, $subtotal);
        }

        // Calculate delivery cost (only for delivery orders)
        $deliveryCost = $orderType === 'delivery' ? $this->calculateDeliveryCost() : 0;

        $total = (float) max(0, $subtotal - $voucherDiscount + $deliveryCost);

        // Get user addresses if authenticated (only for delivery)
        $addresses = collect([]);
        $selectedBranchForDelivery = null;
        
        if (auth('web')->check() && $orderType === 'delivery') {
            // Get the branch that serves the initially selected city
            $userLocation = session('user_location');
            if ($userLocation && isset($userLocation['city_id']) && $userLocation['city_id']) {
                $cityId = (int) $userLocation['city_id'];
                $selectedBranchForDelivery = Branch::where('active', 1)
                    ->whereHas('cities', function($q) use ($cityId) {
                        $q->where('locations.id', $cityId);
                    })
                    ->first();
            }
            
            // Get all user addresses
            $allAddresses = Address::where('user_id', auth('web')->id())
                ->active()
                ->orderBy('is_main', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Check which addresses are available (served by the selected branch)
            $addresses = $allAddresses->map(function($address) use ($selectedBranchForDelivery) {
                $isAvailable = false;
                $addressCityId = null;
                $addressCityName = $address->city; // Default to stored city name
                $addressStateName = $address->state; // Default to stored state name
                
                // If no branch is selected, all addresses are unavailable
                if (!$selectedBranchForDelivery) {
                    // Try to get proper names from Location model
                    if ($address->city) {
                        $city = null;
                        // First, try to match by exact name in current locale
                        $city = Location::where('type', 'city')
                            ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.' . app()->getLocale() . '")) = ?', [trim($address->city)])
                            ->first();
                        
                        // If not found, try to match in all locales
                        if (!$city) {
                            $city = Location::where('type', 'city')
                                ->where(function($q) use ($address) {
                                    $q->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar")) = ?', [trim($address->city)])
                                      ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.en")) = ?', [trim($address->city)]);
                                })
                                ->first();
                        }
                        
                        // If still not found and city field is numeric, treat it as city_id
                        if (!$city && is_numeric($address->city)) {
                            $city = Location::where('type', 'city')
                                ->where('id', (int)$address->city)
                                ->first();
                        }
                        
                        if ($city) {
                            $addressCityName = $city->getTranslation('name', app()->getLocale());
                            $state = $city->parent;
                            if ($state) {
                                $addressStateName = $state->getTranslation('name', app()->getLocale());
                            }
                        }
                    } elseif ($address->state) {
                        // If only state is available, try to get state name
                        $state = null;
                        if (is_numeric($address->state)) {
                            $state = Location::where('type', 'state')
                                ->where('id', (int)$address->state)
                                ->first();
                        } else {
                            $state = Location::where('type', 'state')
                                ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.' . app()->getLocale() . '")) = ?', [trim($address->state)])
                                ->first();
                        }
                        
                        if ($state) {
                            $addressStateName = $state->getTranslation('name', app()->getLocale());
                        }
                    }
                    
                    return [
                        'id' => $address->id,
                        'title' => $address->title,
                        'full_address' => $address->full_address,
                        'type' => $address->type ?? '',
                        'is_main' => $address->is_main,
                        'is_available' => false,
                        'city_id' => null,
                        'city_name' => $addressCityName,
                        'state_name' => $addressStateName,
                    ];
                }
                
                // Try to find city_id from address city name
                if ($address->city) {
                    // First, try to match by exact name in current locale
                    $city = Location::where('type', 'city')
                        ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.' . app()->getLocale() . '")) = ?', [trim($address->city)])
                        ->first();
                    
                    // If not found, try to match in all locales
                    if (!$city) {
                        $city = Location::where('type', 'city')
                            ->where(function($q) use ($address) {
                                $q->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar")) = ?', [trim($address->city)])
                                  ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.en")) = ?', [trim($address->city)]);
                            })
                            ->first();
                    }
                    
                    // If still not found and city field is numeric, treat it as city_id
                    if (!$city && is_numeric($address->city)) {
                        $city = Location::where('type', 'city')
                            ->where('id', (int)$address->city)
                            ->first();
                    }
                    
                    if ($city) {
                        $addressCityId = $city->id;
                        $addressCityName = $city->getTranslation('name', app()->getLocale());
                        $state = $city->parent;
                        if ($state) {
                            $addressStateName = $state->getTranslation('name', app()->getLocale());
                        }
                        
                        // Check if the selected branch serves this city
                        $isAvailable = $selectedBranchForDelivery->cities()
                            ->where('locations.id', $addressCityId)
                            ->exists();
                    }
                } elseif ($address->state) {
                    // If only state is available, try to get state name
                    $state = null;
                    if (is_numeric($address->state)) {
                        $state = Location::where('type', 'state')
                            ->where('id', (int)$address->state)
                            ->first();
                    } else {
                        $state = Location::where('type', 'state')
                            ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.' . app()->getLocale() . '")) = ?', [trim($address->state)])
                            ->first();
                    }
                    
                    if ($state) {
                        $addressStateName = $state->getTranslation('name', app()->getLocale());
                    }
                }
                
                return [
                    'id' => $address->id,
                    'title' => $address->title,
                    'full_address' => $address->full_address,
                    'type' => $address->type ?? '',
                    'is_main' => $address->is_main,
                    'is_available' => $isAvailable,
                    'city_id' => $addressCityId,
                    'city_name' => $addressCityName,
                    'state_name' => $addressStateName,
                ];
            });
            
            // Check if there's at least one available address
            $hasAvailableAddress = $addresses->contains(function($address) {
                return is_array($address) ? ($address['is_available'] ?? false) : ($address->is_available ?? false);
            });
        } else {
            $hasAvailableAddress = false;
        }

        // Get current location from session for address form pre-selection
        $userLocation = session('user_location');
        $selectedStateId = null;
        $selectedCityId = null;
        
        if ($userLocation && isset($userLocation['city_id']) && $userLocation['city_id']) {
            $selectedCityId = (int) $userLocation['city_id'];
            // Get state_id from city
            $city = Location::where('type', 'city')
                ->where('id', $selectedCityId)
                ->first();
            if ($city && $city->parent_id) {
                $selectedStateId = $city->parent_id;
            }
        }

        // Check if restaurant is closed based on working hours
        $restaurantIsClosed = false;
        if ($orderType === 'pick_up' && $selectedBranch) {
            $selectedBranch->load('workingHours');
            $restaurantIsClosed = !$selectedBranch->isCurrentlyOpen();
        } elseif ($orderType === 'delivery') {
            $userLocation = session('user_location');
            $cityId = $userLocation['city_id'] ?? null;
            
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

        return view('website.checkout.index', compact(
            'orderType',
            'branches',
            'selectedBranch',
            'cartProducts',
            'subtotal',
            'voucherDiscount',
            'deliveryCost',
            'total',
            'addresses',
            'selectedBranchForDelivery',
            'hasAvailableAddress',
            'selectedStateId',
            'selectedCityId',
            'restaurantIsClosed'
        ));
    }

    private function getCartProducts($cart)
    {
        return collect($cart)->map(function ($item) {
            $product = Product::where('active', true)
                ->find($item['product_id']);

            if (!$product) return null;

            return [
                'id' => $item['product_id'],
                'name' => $product->name,
                'image' => $product->getFirstMediaUrl('products', 'thumb') ?? asset('website/assets/img/no-image.png'),
                'quantity' => (int) $item['quantity'],
                'price' => (float) $item['price'],
                'addons' => $this->getAddonsData($item['addons'] ?? []),
                'is_box' => (bool) ($item['is_box'] ?? false),
                'box_addons' => $this->getBoxAddonsData($item['box_addons'] ?? []),
            ];
        })->filter();
    }

    private function getAddonsData($addonIds)
    {
        if (empty($addonIds)) return [];
        return \App\Models\Addon::where('active', 1)
            ->whereIn('id', $addonIds)
            ->get(['id','name'])
            ->toArray();
    }

    private function getBoxAddonsData($boxAddons)
    {
        if (empty($boxAddons) || !is_array($boxAddons)) return [];
        $subProductIds = array_keys($boxAddons);
        $subProducts = Product::whereIn('id', $subProductIds)->get(['id','name']);
        $subIdToName = $subProducts->pluck('name','id');
        $result = [];
        foreach ($boxAddons as $subProductId => $addonIds) {
            if (!is_array($addonIds) || empty($addonIds)) continue;
            $addons = \App\Models\Addon::where('active', 1)
                ->whereIn('id', $addonIds)
                ->get(['id','name'])
                ->toArray();
            $result[] = [
                'sub_product_id' => (int) $subProductId,
                'sub_product_name' => (string) ($subIdToName[$subProductId] ?? ''),
                'addons' => $addons,
            ];
        }
        return $result;
    }

    private function calculateVoucherDiscount($appliedVoucher, $subtotal)
    {
        $voucher = \App\Models\Coupon::where('code', $appliedVoucher['code'])
            ->where('active', 1)
            ->first();

        if (!$voucher) return 0;

        $userId = auth('web')->id();
        $minOrderPrice = (float) $voucher->min_order_price;

        if (!$voucher->isValidForUser($userId) || $subtotal < $minOrderPrice) {
            return 0;
        }

        if ($voucher->type === 'percent') {
            return (float) ($subtotal * ((float) $voucher->value / 100));
        }

        return (float) min((float) $voucher->value, $subtotal);
    }

    /**
     * Check if phone number exists in database
     */
    public function checkPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:8|min:8',
            'guest_country_id' => 'required|exists:locations,id',
            'email' => 'required|email|max:255',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $request->input('phone'));
        $countryId = $request->input('guest_country_id');
        $email = $request->input('email');

        // Validate phone length
        if (strlen($phone) !== 8) {
            return response()->json([
                'status' => false,
                'message' => __('website.phone_must_be_8_digits')
            ]);
        }

        // Validate country
        $country = \App\Models\Location::find($countryId);
        if (!$country || $country->type !== 'country') {
            return response()->json([
                'status' => false,
                'message' => __('website.invalid_country')
            ]);
        }

        // Check if phone exists with same country
        $phoneExists = User::where('phone', $phone)
            ->where('country_id', $countryId)
            ->exists();
        $emailExists = User::where('email', $email)->exists();

        if ($phoneExists || $emailExists) {
            return response()->json([
                'status' => false,
                'message' => __('website.phone_or_email_exists'),
                'phone_exists' => $phoneExists,
                'email_exists' => $emailExists
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => __('website.phone_and_email_available')
        ]);
    }

    /**
     * Send OTP to phone number
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:8|min:8',
            'guest_country_id' => 'required|exists:locations,id',
            'email' => 'nullable|email|max:255', // Optional email for fallback
        ]);

        $phone = preg_replace('/[^0-9]/', '', $request->input('phone'));
        $countryId = $request->input('guest_country_id');

        // Validate phone length
        if (strlen($phone) !== 8) {
            return response()->json([
                'status' => false,
                'message' => __('website.phone_must_be_8_digits')
            ]);
        }

        // Get country
        $country = \App\Models\Location::find($countryId);
        if (!$country || $country->type !== 'country') {
            return response()->json([
                'status' => false,
                'message' => __('website.invalid_country')
            ]);
        }

        // Build full phone for SMS (country code + phone)
        $fullPhone = preg_replace('/[^0-9]/', '', $country->phone_code) . $phone;

        // Check if phone exists with same country
        $user = User::where('phone', $phone)
            ->where('country_id', $countryId)
            ->first();
        if ($user) {
            return response()->json([
                'status' => false,
                'message' => __('website.phone_already_registered')
            ]);
        }

        // Generate OTP
        $otp = rand(100000, 999999);

        // Store OTP in session
        session([
            'checkout_otp' => $otp,
            'checkout_phone' => $phone, // Store 8-digit phone
            'checkout_country_id' => $countryId,
            'checkout_otp_expires_at' => Carbon::now()->addMinutes(10)
        ]);

        // Check if OTP test mode is enabled (from .env OTP_TEST_MODE)
        $showOtpInResponse = \App\Helpers\OtpHelper::shouldShowOtpInPopup();
        $skipSms = \App\Helpers\OtpHelper::shouldSkipSms();
        
        // If test mode, skip SMS and show OTP in response
        if ($skipSms) {
            Log::info('SMS OTP skipped in test mode (OTP_TEST_MODE enabled)', [
                'phone' => $phone,
                'otp' => $otp
            ]);

            return response()->json(\App\Helpers\OtpHelper::getTestOtpResponse($otp));
        }

        // Try to send OTP via SMS first
        $smsSent = false;
        try {
            $smsService = new SmsService();
            $smsResult = $smsService->sendOtp($fullPhone, $otp, 'verification');
            
            if ($smsResult['success']) {
                $smsSent = true;
            } else {
                $errorMessage = $smsResult['message'] ?? __('website.sms_sending_failed');
                
                Log::warning('SMS OTP sending failed in checkout', [
                    'phone' => $phone,
                    'error' => $errorMessage,
                    'response' => $smsResult['response'] ?? null,
                    'error_type' => $smsResult['error_type'] ?? null,
                    'status_code' => $smsResult['status_code'] ?? null
                ]);
            }
        } catch (\Exception $e) {
            Log::error('SMS OTP Error in checkout', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // If SMS failed, try to send via email as fallback
        if (!$smsSent) {
            $email = $request->input('email');
            
            if ($email) {
                try {
                    Mail::to($email)->send(new SendOtpMail($otp));
                    
                    Log::info('OTP sent via email as SMS fallback', [
                        'email' => $email,
                        'phone' => $phone
                    ]);
                    
                    return response()->json([
                        'status' => true,
                        'message' => __('website.otp_sent_to_email'),
                        'sent_via' => 'email',
                        'otp' => $showOtpInResponse ? $otp : null,
                        'show_otp' => $showOtpInResponse,
                        'testing_mode' => $showOtpInResponse
                    ]);
                } catch (\Exception $e) {
                    Log::error('Email OTP sending failed', [
                        'email' => $email,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // If both SMS and email failed, return error
            return response()->json([
                'status' => false,
                'message' => __('website.sms_sending_failed') . '. ' . __('website.please_try_again_or_use_email'),
                'error_details' => [
                    'sms_failed' => true,
                    'email_failed' => !$email
                ]
            ]);
        }

        // Return success response - only show OTP if test mode is enabled
        return response()->json([
            'status' => true,
            'message' => __('website.otp_sent_successfully'),
            'sent_via' => 'sms',
            'otp' => $showOtpInResponse ? $otp : null,
            'show_otp' => $showOtpInResponse,
            'testing_mode' => $showOtpInResponse
        ]);
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $otp = $request->input('otp');
        $sessionOtp = session('checkout_otp');
        $otpExpiresAt = session('checkout_otp_expires_at');

        if (!$sessionOtp || !$otpExpiresAt) {
            return response()->json([
                'status' => false,
                'message' => __('website.otp_expired')
            ]);
        }

        if (Carbon::now()->gt($otpExpiresAt)) {
            session()->forget(['checkout_otp', 'checkout_phone', 'checkout_otp_expires_at']);
            return response()->json([
                'status' => false,
                'message' => __('website.otp_expired')
            ]);
        }

        if ($otp != $sessionOtp) {
            return response()->json([
                'status' => false,
                'message' => __('website.invalid_otp')
            ]);
        }

        // Mark OTP as verified
        session(['checkout_otp_verified' => true]);

        return response()->json([
            'status' => true,
            'message' => __('website.otp_verified_successfully')
        ]);
    }

    /**
     * Register guest user after OTP verification
     */
    public function registerGuest(Request $request)
    {
        // Check if OTP is verified
        if (!session('checkout_otp_verified')) {
            return response()->json([
                'status' => false,
                'message' => __('website.please_verify_otp_first')
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:8|min:8',
            'guest_country_id' => 'required|exists:locations,id',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $request->input('phone'));
        $countryId = $request->input('guest_country_id');
        $sessionPhone = session('checkout_phone');
        $sessionCountryId = session('checkout_country_id');

        // Validate phone length
        if (strlen($phone) !== 8) {
            return response()->json([
                'status' => false,
                'message' => __('website.phone_must_be_8_digits')
            ]);
        }

        // Verify phone and country match session
        if ($phone != $sessionPhone || $countryId != $sessionCountryId) {
            return response()->json([
                'status' => false,
                'message' => __('website.phone_mismatch')
            ]);
        }

        // Validate country
        $country = \App\Models\Location::find($countryId);
        if (!$country || $country->type !== 'country') {
            return response()->json([
                'status' => false,
                'message' => __('website.invalid_country')
            ]);
        }

        // Check if phone or email already exists
        $phoneExists = User::where('phone', $phone)
            ->where('country_id', $countryId)
            ->exists();
        $emailExists = User::where('email', $request->input('email'))->exists();

        if ($phoneExists || $emailExists) {
            return response()->json([
                'status' => false,
                'message' => __('website.phone_or_email_exists'),
                'phone_exists' => $phoneExists,
                'email_exists' => $emailExists
            ]);
        }

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name' => $request->input('name'),
                'phone' => $phone,
                'country_id' => $countryId,
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'is_admin' => 0,
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            // Generate OTP for email verification
            $emailOtp = rand(100000, 999999);
            
            DB::table('otps')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'otp_code' => $emailOtp,
                    'expires_at' => Carbon::now()->addMinutes(10),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            // Send email verification
            try {
                Mail::to($user->email)->send(new SendOtpMail($emailOtp));
            } catch (\Exception $e) {
                Log::warning('Email verification sending failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            DB::commit();

            // Clear checkout session
            session()->forget(['checkout_otp', 'checkout_phone', 'checkout_otp_expires_at', 'checkout_otp_verified']);

            // Auto login user
            auth('web')->login($user, true);

            return response()->json([
                'status' => true,
                'message' => __('website.account_created_successfully'),
                'user' => $user
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Guest registration error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => __('website.registration_failed')
            ]);
        }
    }

    /**
     * Calculate delivery cost based on current city
     * Gets shipping_fee_near from locations table for the selected city
     */
    private function calculateDeliveryCost(): float
    {
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
        return (float) ($city->shipping_fee_near ?? 0);
    }
}
