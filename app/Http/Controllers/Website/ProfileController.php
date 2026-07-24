<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Favourite;
use App\Models\Transaction;
use App\Models\Coupon;
use App\Models\Address;
use App\Services\BookeeyService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ProfileController extends Controller
{
    public function getTabContent($tab)
    {
        $user = auth()->user();
        $data = [];

        switch ($tab) {
            case 'profile_data':
                $userId = $user->id;
                $data = [
                    'user' => $user,
                    'coupons' => Coupon::where(function ($query) use ($userId) {
                        $query->where('user_id', $userId)
                              ->orWhereNull('user_id');
                    })
                    ->where('active', 1)
                    ->where(function ($query) {
                        $query->whereNull('expire_at')
                              ->orWhere('expire_at', '>', now());
                    })
                    ->where(function ($query) {
                        $query->whereNull('usage_limit')
                              ->orWhere('usage_limit', '>', 0);
                    })
                    ->orderBy('created_at', 'desc')
                    ->get()
                ];
                break;
            case 'orders':
                $data = [
                    'orders' => Order::where('user_id', $user->id)
                        ->with(['items.product.media', 'branch', 'coupon'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(10)
                ];
                break;
            case 'wallet':
                $wallet = $user->wallet;
                $data = [
                    'walletBalance' => $wallet ? $wallet->balance : 0,
                    'transactions' => Transaction::where('wallet_id', $wallet->id ?? 0)
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get()
                ];
                break;
            case 'track_orders':
                $data = [
                    'trackingOrders' => Order::where('user_id', $user->id)
                        ->whereIn('status', ['pending', 'processing', 'delivered'])
                        ->with(['items.product'])
                        ->orderBy('created_at', 'desc')
                        ->get()
                ];
                break;
            case 'wishlist':
                $data = [
                    'favorites' => Favourite::where('user_id', $user->id)
                        ->with('product')
                        ->get()
                ];
                break;
            case 'addresses':
                $data = [
                    'addresses' => Address::where('user_id', $user->id)
                        ->active()
                        ->orderBy('is_main', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->get()
                ];
                break;
            case 'add_money':
                $data = [];
                break;
            default:
                $data = [];
        }

        $content = view('website.profile.partials.'.$tab, $data)->render();

        return response()->json([
            'content' => $content
        ]);
    }

    public function profile()
    {
        // Redirect to account info page
        return redirect()->route('profile.account-info');
    }

    public function profileData()
    {
        $user = auth()->user();
        $userId = $user->id;
        $data = [
            'user' => $user,
            'coupons' => Coupon::where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhereNull('user_id');
            })
            ->where('active', 1)
            ->where(function ($query) {
                $query->whereNull('expire_at')
                      ->orWhere('expire_at', '>', now());
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit')
                      ->orWhere('usage_limit', '>', 0);
            })
            ->orderBy('created_at', 'desc')
            ->get()
        ];
        return view('website.profile.account-info', $data);
    }

    public function orders()
    {
        $user = auth()->user();
        $data = [
            'orders' => Order::where('user_id', $user->id)
                ->with(['items.product.media', 'branch', 'coupon'])
                ->orderBy('created_at', 'desc')
                ->paginate(10)
        ];
        return view('website.profile.orders', $data);
    }

    public function wallet()
    {
        $user = auth()->user();
        $wallet = $user->wallet;
        $data = [
            'walletBalance' => $wallet ? $wallet->balance : 0,
            'transactions' => Transaction::where('wallet_id', $wallet->id ?? 0)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];
        return view('website.profile.wallet', $data);
    }

    public function addMoneyPage()
    {
        return view('website.profile.add-money');
    }

    public function trackOrders()
    {
        $user = auth()->user();
        $data = [
            'trackingOrders' => Order::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'processing', 'delivered'])
                ->with(['items.product'])
                ->orderBy('created_at', 'desc')
                ->get()
        ];
        return view('website.profile.track-orders', $data);
    }

    public function wishlist()
    {
        $user = auth()->user();
        $data = [
            'favorites' => Favourite::where('user_id', $user->id)
                ->with('product')
                ->get()
        ];
        return view('website.profile.wishlist', $data);
    }

    public function addresses()
    {
        $user = auth()->user();
        $data = [
            'addresses' => Address::where('user_id', $user->id)
                ->active()
                ->orderBy('is_main', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
        ];
        return view('website.profile.addresses', $data);
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = auth()->user();
            $field = $request->input('field');
            $value = $request->input('value');
            $currentPassword = $request->input('current_password');


            $allowedFields = ['name', 'email', 'phone', 'address', 'password'];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'error' => __('website.invalid_field') . ': ' . $field,
                    'allowed_fields' => $allowedFields,
                    'received_field' => $field
                ], 400);
            }
            if ($field === 'email') {
                $request->validate([
                    'value' => [
                        'required',
                        'email',
                        'max:255',
                        Rule::unique('users', 'email')->ignore($user->id),
                    ],
                ]);
            }
            if ($field === 'password') {
                if (!$currentPassword) {
                    return response()->json(['error' => __('website.current_password_required')], 400);
                }

                if (!Hash::check($currentPassword, $user->password)) {
                    return response()->json(['error' => __('website.current_password_incorrect')], 400);
                }

                if (strlen($value) < 6) {
                    return response()->json(['error' => __('website.password_min_length')], 400);
                }

                $value = bcrypt($value);
            }

            if ($field === 'phone') {
                $request->validate([
                    'value' => 'required|string|max:8|min:8',
                    'country_id' => 'required|exists:locations,id',
                    'otp_code' => 'required|string|size:6',
                ]);

                // Get country phone code
                $country = \App\Models\Location::find($request->country_id);
                if (!$country || $country->type !== 'country') {
                    return response()->json(['error' => __('website.invalid_country')], 400);
                }

                // Clean phone number (should be 8 digits)
                $newPhone = preg_replace('/[^0-9]/', '', $request->value);

                // Validate phone length
                if (strlen($newPhone) !== 8) {
                    return response()->json(['error' => __('website.phone_must_be_8_digits')], 400);
                }

                // Get the new phone from session (set when OTP was sent)
                $sessionNewPhone = session('phone_change_new_phone');

                if (!$sessionNewPhone) {
                    return response()->json([
                        'error' => __('website.please_send_otp_first')
                    ], 400);
                }

                // Verify OTP for phone change
                $otpRecord = DB::table('otps')
                    ->where('user_id', $user->id)
                    ->where('otp_code', $request->otp_code)
                    ->first();

                if (!$otpRecord) {
                    return response()->json([
                        'error' => __('website.invalid_otp')
                    ], 400);
                }

                if ($otpRecord->expires_at < now()) {
                    // Clear session on expired OTP
                    session()->forget(['phone_change_new_phone', 'phone_change_otp_sent_at']);
                    return response()->json([
                        'error' => __('website.otp_expired')
                    ], 400);
                }

                // Build full phone for checking (country code + phone)
                $fullPhone = $country->phone_code . $newPhone;
                $fullPhone = preg_replace('/[^0-9]/', '', $fullPhone); // Remove + and spaces

                // Verify that the phone matches the one OTP was sent to
                if ($fullPhone !== preg_replace('/[^0-9]/', '', $sessionNewPhone)) {
                    return response()->json([
                        'error' => __('website.phone_mismatch_otp')
                    ], 400);
                }

                // Check if phone is already taken (check by phone + country_id combination)
                $existingUser = User::where('phone', $newPhone)
                    ->where('country_id', $request->country_id)
                    ->where('id', '!=', $user->id)
                    ->first();

                if ($existingUser) {
                    return response()->json([
                        'error' => __('website.phone_already_taken')
                    ], 400);
                }

                // Update value to cleaned phone (8 digits only)
                $value = $newPhone;

                // Save country_id separately
                $user->country_id = $request->country_id;

                // Delete OTP and clear session after successful verification
                DB::table('otps')->where('user_id', $user->id)->delete();
                session()->forget(['phone_change_new_phone', 'phone_change_otp_sent_at']);
            }

            // Validate name
            if ($field === 'name') {
                $request->validate([
                    'value' => 'required|string|max:255',
                ]);
            }

            // Update the field
            if ($field === 'address') {
                // For address, we might want to store it differently
                // For now, let's assume we have an address field in the user table
                $user->address = $value;
            } else {
                $user->$field = $value;
            }

            $user->save();

            // Return appropriate response based on field type
            if ($field === 'password') {
                return response()->json([
                    'success' => true,
                    'message' => __('website.password_updated_successfully'),
                    'value' => '********************'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => __('website.field_updated_successfully', ['field' => ucfirst($field)]),
                'value' => $value
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => __('website.validation_failed'),
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => __('website.profile_update_error'),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function addMoney(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.001|max:10000'
        ]);

        $user = auth()->user();
        $amount = (float) $request->amount;
        $paymentMethod = $request->input('payment_method');
        $payType = $request->input('pay_type', 'knet'); // Default to knet

        if (!$paymentMethod) {
            return back()->with('error', __('website.please_select_payment_method'));
        }

        // Log payment method and pay_type for debugging
        Log::info('Wallet Top-up Request', [
            'user_id' => $user->id,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'pay_type' => $payType,
            'all_inputs' => $request->all()
        ]);

        try {
            // Store pending payment info in session
            session([
                'wallet_topup_amount' => $amount,
                'wallet_topup_user_id' => $user->id
            ]);

            // Use same logic as OrderController
            switch ($paymentMethod) {
                case 'knet':
                    return $this->processBookeeyPayment($user, $amount, 'knet');

                case 'credit':
                    return $this->processBookeeyPayment($user, $amount, 'credit');

                case 'amex':
                    return $this->processBookeeyPayment($user, $amount, 'amex');

                case 'applepay':
                    return $this->processBookeeyPayment($user, $amount, 'applepay');

                default:
                    return back()->with('error', __('website.invalid_payment_method'));
            }

        } catch (\Exception $e) {
            Log::error('Wallet Top-up Error', [
                'user_id' => $user->id,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', __('website.wallet_topup_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Process Bookeey payment for wallet top-up
     */
    private function processBookeeyPayment($user, $amount, $payType = 'knet')
    {
        try {
            $bookeey = new BookeeyService();

            // Clean phone number
            $phone = preg_replace('/[^0-9]/', '', $user->phone ?? '');

            // Ensure amount is formatted correctly
            $amountFormatted = number_format((float)$amount, 3, '.', '');

            // Generate unique reference
            $reference = 'WALLET-' . $user->id . '-' . time();

            session(['wallet_topup_reference' => $reference]);

            $invoiceData = [
                'CustomerName'       => $user->name,
                'InvoiceValue'       => $amountFormatted,
                'CustomerEmail'      => $user->email,
                'CallBackUrl'        => route('website.wallet.callback'),
                'ErrorUrl'           => route('website.wallet.failed'),
                'CustomerMobile'     => $phone,
                'CustomerReference'  => $reference,
                'pay_type'           => $payType, // Pass pay_type to Bookeey service
                // 'UserDefinedField'   => 'Wallet-Topup-' . $user->id,
            ];

            Log::info('Wallet Top-up Payment Initiated (Bookeey)', [
                'reference' => $reference,
                'amount' => $amount,
                'user_id' => $user->id,
                'pay_type' => $payType,
                'invoice_data' => $invoiceData
            ]);

            $payment = $bookeey->createInvoice($invoiceData);

            if ($payment['success'] && !empty($payment['invoiceURL'])) {
                // Store trackId or paymentId in session for status checking
                $paymentId = $payment['trackId'] ?? $payment['paymentId'] ?? $payment['invoiceId'] ?? null;
                if ($paymentId) {
                    session(['wallet_topup_payment_id' => $paymentId]);
                }

                return redirect($payment['invoiceURL']);
            }

            // Log the error response
            Log::error('Bookeey Payment Failed - No Invoice URL', [
                'payment_response' => $payment,
                'pay_type' => $payType,
                'user_id' => $user->id,
                'amount' => $amount
            ]);

            $errorMessage = $payment['error'] ?? 'Invoice creation failed';
            if (isset($payment['data']['ErrorMessage'])) {
                $errorMessage = $payment['data']['ErrorMessage'];
            }

            throw new \Exception($errorMessage);

        } catch (\Exception $e) {
            Log::error('Bookeey Wallet Payment Error', [
                'amount' => $amount,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('website.wallet.failed')
                ->with('error', __('website.payment_initiation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Handle payment callback from Bookeey
     */
    public function walletCallback(Request $request)
    {
        $amount = session('wallet_topup_amount');
        $userId = session('wallet_topup_user_id');
        $reference = session('wallet_topup_reference');

        if (!$amount || !$userId) {
            return redirect()->route('website.profile')
                ->with('error', __('website.invalid_payment_session'));
        }

        try {
            $bookeey = new BookeeyService();
            // Bookeey uses reference (MerchantTxnRefNo) for status check
            // TrackId may come in callback but we use reference for status check
            $trackId = $request->input('TrackId') ?? $request->input('trackId') ?? $request->input('paymentId');
            if ($trackId) {
                session(['wallet_topup_payment_id' => $trackId]);
            }

            $reference = session('wallet_topup_reference');
            if (!$reference) {
                throw new \Exception('Reference not found in session');
            }

            $user = User::findOrFail($userId);

            // Use reference for status check (MerchantTxnRefNo)
            $payment = $bookeey->getPaymentStatus($reference);

            if ($payment['success'] && ($payment['status'] == 'Paid' || $payment['status'] == 'Captured')) {
                // Payment successful - add money to wallet
                $wallet = $user->wallet;
                if (!$wallet) {
                    $wallet = $user->createWallet();
                }

                try {
                    // Get payment ID from response or session
                    $paymentId = $payment['paymentStatus']['PaymentId'] ?? $payment['paymentStatus']['BankRefNo'] ?? session('wallet_topup_payment_id') ?? $trackId ?? '';

                    // Deposit money to wallet with Bookeey metadata
                    $wallet->deposit($amount, [
                        'description' => 'Wallet top-up via Bookeey',
                        'payment_method' => 'bookeey',
                        'payment_id' => $paymentId,
                        'reference' => $reference
                    ]);

                    Log::info('Wallet Top-up Successful', [
                        'reference' => $reference,
                        'user_id' => $user->id,
                        'amount' => $amount,
                        'payment_id' => $paymentId
                    ]);

                    // Clear session
                    session()->forget(['wallet_topup_amount', 'wallet_topup_user_id', 'wallet_topup_reference', 'wallet_topup_payment_id']);

                    return redirect()->route('website.wallet.success')
                        ->with('success', __('website.wallet_topup_successful'))
                        ->with('topup_amount', $amount)
                        ->with('new_balance', $wallet->balance);

                } catch (\Exception $e) {
                    Log::error('Wallet Deposit Error', [
                        'reference' => $reference,
                        'error' => $e->getMessage()
                    ]);

                    return redirect()->route('website.wallet.failed')
                        ->with('error', __('website.wallet_deposit_failed'))
                        ->with('amount', $amount);
                }
            } else {
                // Payment failed
                Log::warning('Wallet Top-up Payment Failed', [
                    'reference' => $reference,
                    'status' => $payment['status'] ?? 'unknown',
                    'user_id' => $userId
                ]);

                return redirect()->route('website.wallet.failed')
                    ->with('error', __('website.payment_failed') . ' - Status: ' . ($payment['status'] ?? 'Unknown'))
                    ->with('amount', $amount);
            }

        } catch (\Exception $e) {
            Log::error('Wallet Payment Callback Error', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('website.wallet.failed')
                ->with('error', __('website.payment_verification_failed') . ': ' . $e->getMessage())
                ->with('amount', $amount);
        }
    }

    /**
     * Show wallet top-up success page
     */
    public function walletSuccess()
    {
        $user = auth()->user();
        $walletBalance = $user->wallet ? $user->wallet->balance : 0;
        $topupAmount = session('topup_amount', 0);

        return view('website.profile.wallet-success', compact('walletBalance', 'topupAmount'));
    }

    /**
     * Show wallet top-up failed page
     */
    public function walletFailed()
    {
        $user = auth()->user();
        $amount = session('amount', session('wallet_topup_amount', 0));

        return view('website.profile.wallet-failed', compact('amount'));
    }

    public function addFavorite(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $user = auth()->user();

        // Check if already favorited
        $existing = Favourite::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => __('website.already_in_favorites'),
                'is_favorited' => true
            ]);
        }

        // Add to favorites
        Favourite::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id
        ]);

        return response()->json([
            'success' => true,
            'message' => __('website.added_to_favorites'),
            'is_favorited' => true
        ]);
    }

    public function removeFavorite(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        Favourite::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => __('website.removed_from_favorites'),
            'is_favorited' => false
        ]);
    }

    /**
     * Send OTP for phone number change
     */
    public function sendPhoneChangeOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'country_id' => 'required|exists:locations,id',
        ]);

        $user = auth()->user();
        $newPhone = $request->phone;
        $countryId = $request->country_id;

        // Get country
        $country = \App\Models\Location::find($countryId);
        if (!$country || $country->type !== 'country') {
            return response()->json([
                'success' => false,
                'error' => __('website.invalid_country')
            ], 400);
        }

        // Clean phone number (should be 8 digits)
        $phoneDigits = preg_replace('/[^0-9]/', '', $newPhone);

        if (strlen($phoneDigits) !== 8) {
            return response()->json([
                'success' => false,
                'error' => __('website.phone_must_be_8_digits')
            ], 400);
        }

        // Build full phone for checking (country code + phone)
        $fullPhone = preg_replace('/[^0-9]/', '', $country->phone_code) . $phoneDigits;

        // Check if phone is already taken (check by phone + country_id combination)
        $existingUser = User::where('phone', $phoneDigits)
            ->where('country_id', $countryId)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'error' => __('website.phone_already_taken')
            ], 400);
        }

        try {
            $otp = rand(100000, 999999);

            DB::table('otps')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'otp_code' => $otp,
                    'expires_at' => Carbon::now()->addMinutes(10),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            // Store new phone in session for verification (full phone with country code)
            session([
                'phone_change_new_phone' => $fullPhone,
                'phone_change_otp_sent_at' => now(),
                'phone_change_country_id' => $countryId
            ]);

            // Send OTP via SMS to the new phone number (full phone with country code)
            $smsService = new SmsService();
            $smsResult = $smsService->sendOtp($fullPhone, $otp, 'phone_change');

            if (!$smsResult['success']) {
                $errorMessage = $smsResult['message'] ?? __('website.sms_sending_failed');

                // Clear session on failure
                session()->forget(['phone_change_new_phone', 'phone_change_otp_sent_at']);

                Log::error('SMS OTP sending failed for phone change', [
                    'user_id' => $user->id,
                    'new_phone' => $newPhone,
                    'error' => $errorMessage,
                    'response' => $smsResult['response'] ?? null,
                    'error_type' => $smsResult['error_type'] ?? null,
                    'error_code' => $smsResult['error_code'] ?? null,
                    'status_code' => $smsResult['status_code'] ?? null
                ]);

                return response()->json([
                    'success' => false,
                    'error' => $errorMessage
                ], 500);
            }

            // Format phone for display (country code + phone)
            $phoneDisplay = $country->phone_code . ' ' . $phoneDigits;

            return response()->json([
                'success' => true,
                'message' => __('website.otp_sent_to_phone'),
                'phone_full' => $phoneDisplay
            ]);

        } catch (\Exception $e) {
            Log::error('Phone Change OTP Error', [
                'user_id' => $user->id,
                'new_phone' => $newPhone,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => __('website.otp_send_error') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert points to wallet
     */
    public function convertPointsToWallet(Request $request)
    {
        $user = auth()->user();

        // Get settings
        $pointsPerKd = (float) \App\Models\Setting::getValue('points_per_kd', null, 100);
        $minimumPointsToConvert = (int) \App\Models\Setting::getValue('minimum_points_to_convert', null, 100);

        // Validate settings
        if ($pointsPerKd <= 0) {
            return response()->json([
                'success' => false,
                'message' => __('website.points_conversion_not_configured')
            ], 400);
        }

        // Check user has enough points
        $userPoints = $user->points ?? 0;
        if ($userPoints < $minimumPointsToConvert) {
            return response()->json([
                'success' => false,
                'message' => __('website.insufficient_points_to_convert', ['minimum' => $minimumPointsToConvert])
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Calculate amount in KD
            $amountInKd = $userPoints / $pointsPerKd;

            // Ensure wallet exists
            $wallet = $user->wallet;
            if (!$wallet) {
                $wallet = $user->createWallet();
            }

            // Add money to wallet
            $transaction = $wallet->deposit($amountInKd, [
                'notes' => __('website.points_converted_to_wallet', [
                    'points' => $userPoints,
                    'amount' => number_format($amountInKd, 3)
                ])
            ]);

            // Deduct points from user
            $user->points = 0;
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('website.points_converted_successfully', [
                    'points' => $userPoints,
                    'amount' => number_format($amountInKd, 3)
                ]),
                'new_balance' => number_format($wallet->balance, 3),
                'new_points' => 0
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Points Conversion Error', [
                'user_id' => $user->id,
                'points' => $userPoints,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('website.points_conversion_failed') . ': ' . $e->getMessage()
            ], 500);
        }
    }
}
