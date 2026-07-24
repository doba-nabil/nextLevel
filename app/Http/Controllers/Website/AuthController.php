<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // login functions
    public function login(): View
    {
        return view('website.auth.login');
    }

    public function login_post(LoginRequest $request): RedirectResponse
    {
        $loginField = $request->input('login'); // Can be email or phone
        $password = $request->input('password');
        
        // Determine if login field is email or phone
        $isEmail = filter_var($loginField, FILTER_VALIDATE_EMAIL);
        
        if ($isEmail) {
            // Login with email
            $user = User::where('email', $loginField)
                ->where('is_admin', 0)
                ->first();
            
            if (!$user || !Hash::check($password, $user->password)) {
                return back()->withErrors([
                    'login' => __('auth.failed'),
                ])->withInput($request->only('login'));
            }
            
            // Check if user is pending (not activated) - redirect to OTP verification
            if ($user->status === 'pending') {
                // Generate new OTP
                $otp = rand(100000, 999999);
                
                DB::table('otps')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'otp_code'   => $otp,
                        'expires_at' => Carbon::now()->addMinutes(10),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
                
                // Build full phone for SMS (country code + phone)
                $fullPhone = $user->country
                    ? preg_replace('/[^0-9]/', '', $user->country->phone_code) . $user->phone
                    : $user->phone;
                
                // Check if OTP test mode is enabled
                $skipSms = \App\Helpers\OtpHelper::shouldSkipSms();
                
                if ($skipSms) {
                    Log::info('SMS OTP skipped in test mode (OTP_TEST_MODE enabled) - Login Resend', [
                        'user_id' => $user->id,
                        'phone' => $fullPhone,
                        'otp' => $otp
                    ]);
                    if ($user->email) {
                        Mail::to($user->email)->send(new SendOtpMail($otp));
                    }
                } else {
                    // Send OTP via SMS
                    try {
                        $smsService = new SmsService();
                        $smsResult = $smsService->sendOtp($fullPhone, $otp, 'registration');
                        
                        if (!$smsResult['success'] && $user->email) {
                            Mail::to($user->email)->send(new SendOtpMail($otp));
                        }
                    } catch (\Exception $e) {
                        Log::error('SMS Service Error - Login Resend', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                        if ($user->email) {
                            Mail::to($user->email)->send(new SendOtpMail($otp));
                        }
                    }
                }
                
                // Store phone and country_id in session for verification
                session(['otp_verification_phone' => $user->phone, 'otp_verification_country_id' => $user->country_id]);
                
                return redirect()->route('website.otp.verify.phone')
                    ->with('info', __('website.account_not_activated_otp_sent'));
            }
            
            // Check email verification - only if user has email and is active
            if ($user->email && !$user->hasVerifiedEmail()) {
                return back()->withErrors([
                    'login' => __('website.email_not_verified'),
                ])->withInput($request->only('login'));
            }
            
            Auth::guard('web')->login($user, $request->boolean('remember'));
        } else {
            // Login with phone - find user by phone only (country is optional)
            $phone = preg_replace('/[^0-9]/', '', $loginField);
            
            // Validate phone length
            if (strlen($phone) !== 8) {
                return back()->withErrors([
                    'login' => __('website.phone_must_be_8_digits'),
                ])->withInput($request->only('login'));
            }
            
            $countryId = $request->input('country_id');
            
            // Try to find user by phone and country if country is provided
            $query = User::where('phone', $phone)
                ->where('is_admin', 0);
            
            if ($countryId) {
                $query->where('country_id', $countryId);
            }
            
            $user = $query->first();
            
            // If not found with country, try without country
            if (!$user && $countryId) {
                $user = User::where('phone', $phone)
                    ->where('is_admin', 0)
                    ->first();
            }
            
            if (!$user || !Hash::check($password, $user->password)) {
                return back()->withErrors([
                    'login' => __('auth.failed'),
                ])->withInput($request->only('login'));
            }
            
            // Check if user is pending (not activated) - redirect to OTP verification
            if ($user->status === 'pending') {
                // Generate new OTP
                $otp = rand(100000, 999999);
                
                DB::table('otps')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'otp_code'   => $otp,
                        'expires_at' => Carbon::now()->addMinutes(10),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
                
                // Build full phone for SMS (country code + phone)
                $fullPhone = $user->country
                    ? preg_replace('/[^0-9]/', '', $user->country->phone_code) . $user->phone
                    : $user->phone;
                
                // Check if OTP test mode is enabled
                $skipSms = \App\Helpers\OtpHelper::shouldSkipSms();
                
                if ($skipSms) {
                    Log::info('SMS OTP skipped in test mode (OTP_TEST_MODE enabled) - Login Resend', [
                        'user_id' => $user->id,
                        'phone' => $fullPhone,
                        'otp' => $otp
                    ]);
                    if ($user->email) {
                        Mail::to($user->email)->send(new SendOtpMail($otp));
                    }
                } else {
                    // Send OTP via SMS
                    try {
                        $smsService = new SmsService();
                        $smsResult = $smsService->sendOtp($fullPhone, $otp, 'registration');
                        
                        if (!$smsResult['success'] && $user->email) {
                            Mail::to($user->email)->send(new SendOtpMail($otp));
                        }
                    } catch (\Exception $e) {
                        Log::error('SMS Service Error - Login Resend', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                        if ($user->email) {
                            Mail::to($user->email)->send(new SendOtpMail($otp));
                        }
                    }
                }
                
                // Store phone and country_id in session for verification
                session(['otp_verification_phone' => $user->phone, 'otp_verification_country_id' => $user->country_id]);
                
                return redirect()->route('website.otp.verify.phone')
                    ->with('info', __('website.account_not_activated_otp_sent'));
            }
            
            Auth::guard('web')->login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            
            return redirect()->intended(route('website.home'));
        }
        
        $request->session()->regenerate();
        return redirect()->intended(route('website.home'));
    }

    // register functions
    public function register(): View
    {
        return view('website.auth.register');
    }

    public function register_post(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:8', 'min:8'],
            'country_id' => ['required', 'exists:locations,id'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
        ]);

        // Validate country
        $country = \App\Models\Location::find($request->country_id);
        if (!$country || $country->type !== 'country') {
            return back()->withErrors(['country_id' => __('website.invalid_country')]);
        }

        // Clean phone number (should be 8 digits)
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        if (strlen($phone) !== 8) {
            return back()->withErrors(['phone' => __('website.phone_must_be_8_digits')]);
        }

        // Check if phone is already taken
        $existingUser = User::where('phone', $phone)
            ->where('country_id', $request->country_id)
            ->first();

        if ($existingUser) {
            return back()->withErrors(['phone' => __('website.phone_already_taken')]);
        }

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'phone' => $phone,
                'country_id' => $request->country_id,
                'email' => $request->email ?: null,
                'is_admin' => 0,
                'status' => 'pending',
                'password' => Hash::make($request->password),
            ]);

            // Generate OTP for phone verification
            $otp = rand(100000, 999999);

            DB::table('otps')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'otp_code'   => $otp,
                    'expires_at' => Carbon::now()->addMinutes(10),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            DB::commit();

            // Don't send email verification - we use phone OTP verification instead
            // event(new Registered($user));

            // Build full phone for SMS (country code + phone)
            $fullPhone = $user->country
                ? preg_replace('/[^0-9]/', '', $user->country->phone_code) . $user->phone
                : $user->phone;

            // Check if OTP test mode is enabled (from .env OTP_TEST_MODE)
            $skipSms = \App\Helpers\OtpHelper::shouldSkipSms();

            if ($skipSms) {
                Log::info('SMS OTP skipped in test mode (OTP_TEST_MODE enabled) - Registration', [
                    'user_id' => $user->id,
                    'phone' => $fullPhone,
                    'otp' => $otp
                ]);
                // In test mode, send via email if available, otherwise just log
                if ($user->email) {
                    Mail::to($user->email)->send(new SendOtpMail($otp));
                }
            } else {
                // Send OTP via SMS
                try {
                    $smsService = new SmsService();
                    $smsResult = $smsService->sendOtp($fullPhone, $otp, 'registration');

                    if (!$smsResult['success']) {
                        $errorMessage = $smsResult['message'] ?? __('website.sms_sending_failed');

                        Log::warning('SMS OTP sending failed, falling back to email', [
                            'user_id' => $user->id,
                            'error' => $errorMessage,
                            'response' => $smsResult['response'] ?? null,
                            'error_type' => $smsResult['error_type'] ?? null,
                            'error_code' => $smsResult['error_code'] ?? null,
                            'status_code' => $smsResult['status_code'] ?? null
                        ]);
                        // Fallback to email if SMS fails and email exists
                        if ($user->email) {
                            Mail::to($user->email)->send(new SendOtpMail($otp));
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('SMS Service Error', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Fallback to email if SMS service fails and email exists
                    if ($user->email) {
                        Mail::to($user->email)->send(new SendOtpMail($otp));
                    }
                }
            }

            // Redirect to OTP verification
            // Use phone for verification (store phone in session for verification)
            session(['otp_verification_phone' => $phone, 'otp_verification_country_id' => $request->country_id]);
            
            return redirect()->route('website.otp.verify.phone')
                ->with('success', __('website.otp_sent_to_phone'));

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withErrors(['register' => __('website.account_creation_error')]);
        }
    }

    // logout function
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function verify_form($user_email)
    {
        $user = User::where('email', $user_email)->firstOrFail();
        if($user->email_verified_at){
            return redirect(route('website.login'))->withError(__('website.verified_email'));
        }

        // Get OTP from database to show in test mode
        $otpRecord = DB::table('otps')->where('user_id', $user->id)->first();
        $showOtp = \App\Helpers\OtpHelper::shouldShowOtpInPopup();
        $otpCode = $showOtp && $otpRecord ? $otpRecord->otp_code : null;

        return view('website.auth.verify_otp', [
            'user_email' => $user_email,
            'show_otp' => $showOtp,
            'otp_code' => $otpCode
        ]);
    }

    public function verifyOtp(Request $request, $user_email): RedirectResponse
    {
        $validated = $request->validate([
            'otp_code' => 'required|string',
        ]);

        $user = User::where('email', $user_email)->firstOrFail();


        $otp = DB::table('otps')->where('user_id', $user->id)->first();

        if (! $otp) {
            return back()->withError(__('website.no_otp'));
        }

        if ($otp->expires_at < now()) {
            return back()->withError(__('website.otp_expired'));
        }

        if ($otp->otp_code !== $validated['otp_code']) {
            return back()->withError(__('website.otp_wrong'));
        }

        DB::table('users')->where('id', $user->id)->update(['email_verified_at' => now(), 'status'=>'active']);
        DB::table('otps')->where('user_id', $user->id)->delete();

        return redirect(route('website.login'))->withSuccess(__('website.success_verified'));
    }

    public function verify_phone_form()
    {
        $phone = session('otp_verification_phone');
        $countryId = session('otp_verification_country_id');

        if (!$phone || !$countryId) {
            return redirect()->route('website.register')
                ->withErrors(['otp' => __('website.session_expired_please_register_again')]);
        }

        $user = User::where('phone', $phone)
            ->where('country_id', $countryId)
            ->where('status', 'pending')
            ->first();

        if (!$user) {
            return redirect()->route('website.register')
                ->withErrors(['otp' => __('website.user_not_found')]);
        }

        // Get OTP from database to show in test mode
        $otpRecord = DB::table('otps')->where('user_id', $user->id)->first();
        $showOtp = \App\Helpers\OtpHelper::shouldShowOtpInPopup();
        $otpCode = $showOtp && $otpRecord ? $otpRecord->otp_code : null;

        return view('website.auth.verify_otp_phone', [
            'phone' => $phone,
            'country_id' => $countryId,
            'show_otp' => $showOtp,
            'otp_code' => $otpCode
        ]);
    }

    public function verifyOtpPhone(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'otp_code' => 'required|string',
        ]);

        $phone = session('otp_verification_phone');
        $countryId = session('otp_verification_country_id');

        if (!$phone || !$countryId) {
            return redirect()->route('website.register')
                ->withErrors(['otp' => __('website.session_expired_please_register_again')]);
        }

        $user = User::where('phone', $phone)
            ->where('country_id', $countryId)
            ->where('status', 'pending')
            ->first();

        if (!$user) {
            return redirect()->route('website.register')
                ->withErrors(['otp' => __('website.user_not_found')]);
        }

        $otp = DB::table('otps')->where('user_id', $user->id)->first();

        if (!$otp) {
            return back()->withErrors(['otp_code' => __('website.no_otp')]);
        }

        if ($otp->expires_at < now()) {
            return back()->withErrors(['otp_code' => __('website.otp_expired')]);
        }

        if ($otp->otp_code !== $validated['otp_code']) {
            return back()->withErrors(['otp_code' => __('website.otp_wrong')]);
        }

        // Verify user and activate account
        DB::table('users')->where('id', $user->id)->update([
            'status' => 'active',
            'email_verified_at' => now() // Mark as verified since phone is verified
        ]);
        DB::table('otps')->where('user_id', $user->id)->delete();

        // Clear session
        session()->forget(['otp_verification_phone', 'otp_verification_country_id']);

        // Auto login user
        Auth::guard('web')->login($user, false);
        $request->session()->regenerate();

        return redirect()->intended(route('website.home'))
            ->with('success', __('website.success_verified'));
    }

    public function resendPhone(Request $request)
    {
        try {
            $phone = session('otp_verification_phone');
            $countryId = session('otp_verification_country_id');

            if (!$phone || !$countryId) {
                return response()->json([
                    'status' => 404,
                    'message' => __('website.session_expired_please_register_again'),
                ]);
            }

            $user = User::where('phone', $phone)
                ->where('country_id', $countryId)
                ->where('status', 'pending')
                ->first();

            if (!$user) {
                return response()->json([
                    'status' => 404,
                    'message' => __('website.user_not_found'),
                ]);
            }

            $otp = rand(100000, 999999);

            DB::table('otps')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'otp_code'   => $otp,
                    'expires_at' => Carbon::now()->addMinutes(10),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            // Build full phone for SMS (country code + phone)
            $fullPhone = $user->country
                ? preg_replace('/[^0-9]/', '', $user->country->phone_code) . $user->phone
                : $user->phone;

            // Check if OTP test mode is enabled (from .env OTP_TEST_MODE)
            $skipSms = \App\Helpers\OtpHelper::shouldSkipSms();
            $showOtpInResponse = \App\Helpers\OtpHelper::shouldShowOtpInPopup();

            if ($skipSms) {
                Log::info('SMS OTP skipped in test mode (OTP_TEST_MODE enabled) - Resend Phone', [
                    'user_id' => $user->id,
                    'phone' => $fullPhone,
                    'otp' => $otp
                ]);
                // In test mode, send via email if available
                if ($user->email) {
                    Mail::to($user->email)->send(new SendOtpMail($otp));
                }

                return response()->json([
                    'status' => 200,
                    'message' => __('website.otp_resent_successfully'),
                    'otp' => $showOtpInResponse ? $otp : null,
                    'show_otp' => $showOtpInResponse,
                    'testing_mode' => $showOtpInResponse
                ]);
            } else {
                // Send OTP via SMS
                try {
                    $smsService = new SmsService();
                    $smsResult = $smsService->sendOtp($fullPhone, $otp, 'registration');

                    if (!$smsResult['success']) {
                        $errorMessage = $smsResult['message'] ?? __('website.sms_sending_failed');

                        Log::warning('SMS OTP sending failed, falling back to email', [
                            'user_id' => $user->id,
                            'error' => $errorMessage,
                        ]);
                        // Fallback to email if SMS fails and email exists
                        if ($user->email) {
                            Mail::to($user->email)->send(new SendOtpMail($otp));
                        }
                    }

                    return response()->json([
                        'status' => 200,
                        'message' => __('website.otp_resent_successfully'),
                        'otp' => $showOtpInResponse ? $otp : null,
                        'show_otp' => $showOtpInResponse,
                        'testing_mode' => $showOtpInResponse
                    ]);
                } catch (\Exception $e) {
                    Log::error('SMS Service Error - Resend Phone', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Fallback to email if SMS service fails and email exists
                    if ($user->email) {
                        Mail::to($user->email)->send(new SendOtpMail($otp));
                    }

                    return response()->json([
                        'status' => 200,
                        'message' => __('website.otp_resent_successfully'),
                        'otp' => $showOtpInResponse ? $otp : null,
                        'show_otp' => $showOtpInResponse,
                        'testing_mode' => $showOtpInResponse
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Resend Phone OTP Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 500,
                'message' => __('website.something_went_wrong'),
            ], 500);
        }
    }

    public function resend($user_email)
    {
        try {
            $user = User::where('email', $user_email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 404,
                    'message' => __('website.user_not_found'),
                ]);
            }

            $otp = rand(100000, 999999);

            DB::table('otps')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'otp_code'   => $otp,
                    'expires_at' => Carbon::now()->addMinutes(10),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            // Check if OTP test mode is enabled (from .env OTP_TEST_MODE)
            $skipSms = \App\Helpers\OtpHelper::shouldSkipSms();
            $showOtpInResponse = \App\Helpers\OtpHelper::shouldShowOtpInPopup();

            if ($skipSms) {
                Log::info('SMS OTP skipped in test mode (OTP_TEST_MODE enabled) - Resend', [
                    'user_id' => $user->id,
                    'otp' => $otp
                ]);
                // In test mode, just send via email
                Mail::to($user->email)->send(new SendOtpMail($otp));

                return response()->json([
                    'status' => 200,
                    'message' => __('website.otp_resent_successfully'),
                    'otp' => $showOtpInResponse ? $otp : null,
                    'show_otp' => $showOtpInResponse,
                    'testing_mode' => $showOtpInResponse
                ]);
            } else {
                // Send OTP via SMS
                try {
                    // Build full phone for SMS (country code + phone)
                    $fullPhone = $user->country
                        ? preg_replace('/[^0-9]/', '', $user->country->phone_code) . $user->phone
                        : $user->phone;

                    $smsService = new SmsService();
                    $smsResult = $smsService->sendOtp($fullPhone, $otp, 'registration');

                    if (!$smsResult['success']) {
                        $errorMessage = $smsResult['message'] ?? __('website.sms_sending_failed');

                        Log::warning('SMS OTP resend failed, falling back to email', [
                            'user_id' => $user->id,
                            'error' => $errorMessage,
                            'response' => $smsResult['response'] ?? null,
                            'error_type' => $smsResult['error_type'] ?? null,
                            'error_code' => $smsResult['error_code'] ?? null,
                            'status_code' => $smsResult['status_code'] ?? null
                        ]);
                        // Fallback to email if SMS fails
                        Mail::to($user->email)->send(new SendOtpMail($otp));
                    }
                } catch (\Exception $e) {
                    Log::error('SMS Service Error on Resend', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Fallback to email if SMS service fails
                    Mail::to($user->email)->send(new SendOtpMail($otp));
                }

                return response()->json([
                    'status' => 200,
                    'message' => __('website.otp_resent_successfully'),
                    'otp' => null, // Don't show OTP when test mode is false
                    'show_otp' => false,
                    'testing_mode' => false
                ]);
            }
        } catch (\Exception $e) {
            Log::error('OTP resend failed', [
                'email' => $user_email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 500,
                'message' => __('website.otp_send_error') . ': ' . $e->getMessage(),
            ]);
        }
    }

    public function forget_password()
    {
        return view('website.auth.forget_password');
    }

    public function forget_password_post(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:8', 'min:8'],
            'country_id' => ['required', 'exists:locations,id'],
        ]);

        // Validate country
        $country = \App\Models\Location::find($request->country_id);
        if (!$country || $country->type !== 'country') {
            return back()->withErrors(['country_id' => __('website.invalid_country')]);
        }

        // Clean phone number (should be 8 digits)
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        if (strlen($phone) !== 8) {
            return back()->withErrors(['phone' => __('website.phone_must_be_8_digits')]);
        }

        // Find user by phone and country
        $user = User::where('phone', $phone)
            ->where('country_id', $request->country_id)
            ->where('is_admin', 0)
            ->first();

        if (!$user) {
            return back()->withErrors(['phone' => __('website.user_not_found_with_phone')]);
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

            // Build full phone for SMS (country code + phone)
            $fullPhone = $user->country
                ? preg_replace('/[^0-9]/', '', $user->country->phone_code) . $user->phone
                : $user->phone;

            // Check if OTP test mode is enabled (from .env OTP_TEST_MODE)
            $skipSms = \App\Helpers\OtpHelper::shouldSkipSms();

            if ($skipSms) {
                Log::info('SMS OTP skipped in test mode (OTP_TEST_MODE enabled) - Password Reset', [
                    'user_id' => $user->id,
                    'phone' => $fullPhone,
                    'otp' => $otp
                ]);
                // In test mode, continue without SMS
            } else {
                // Send OTP via SMS
                $smsService = new SmsService();
                $smsResult = $smsService->sendOtp($fullPhone, $otp, 'password_reset');

                if (!$smsResult['success']) {
                    $errorMessage = $smsResult['message'] ?? __('website.sms_sending_failed');

                    Log::warning('SMS OTP sending failed for password reset', [
                        'user_id' => $user->id,
                        'error' => $errorMessage,
                        'response' => $smsResult['response'] ?? null,
                        'error_type' => $smsResult['error_type'] ?? null,
                        'error_code' => $smsResult['error_code'] ?? null,
                        'status_code' => $smsResult['status_code'] ?? null
                    ]);
                    return back()->withErrors(['phone' => __('website.sms_send_failed')]);
                }
            }

            // Store user phone and country_id in session for OTP verification
            session([
                'password_reset_user_id' => $user->id,
                'password_reset_phone' => $phone,
                'password_reset_country_id' => $request->country_id
            ]);

            return redirect()->route('website.forget_pass.otp.verify')
                ->with('success', __('website.otp_sent_to_phone'));

        } catch (\Exception $e) {
            Log::error('Password Reset OTP Error', [
                'phone' => $phone,
                'country_id' => $request->country_id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['phone' => __('website.otp_send_error')]);
        }
    }

    public function forgetPasswordOtpVerify()
    {
        if (!session('password_reset_user_id')) {
            return redirect()->route('website.forget_pass')
                ->withErrors(['phone' => __('website.session_expired')]);
        }

        return view('website.auth.forget_password_otp_verify');
    }

    public function forgetPasswordOtpVerifyPost(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
        ]);

        $userId = session('password_reset_user_id');

        if (!$userId) {
            return redirect()->route('website.forget_pass')
                ->withErrors(['phone' => __('website.session_expired')]);
        }

        $user = User::findOrFail($userId);
        $otp = DB::table('otps')->where('user_id', $user->id)->first();

        if (!$otp) {
            return back()->withErrors(['otp_code' => __('website.no_otp')]);
        }

        if ($otp->expires_at < now()) {
            return back()->withErrors(['otp_code' => __('website.otp_expired')]);
        }

        if ($otp->otp_code !== $request->otp_code) {
            return back()->withErrors(['otp_code' => __('website.otp_wrong')]);
        }

        // OTP verified - generate password reset token
        if ($user->email) {
            $token = Password::createToken($user);
        } else {
            // For users without email, create custom token
            $token = Str::random(64);
            $hashedToken = Hash::make($token);
            $emailIdentifier = 'phone_' . $user->phone . '@reset.local';
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $emailIdentifier],
                [
                    'token' => $hashedToken,
                    'created_at' => now(),
                ]
            );
        }
        DB::table('otps')->where('user_id', $user->id)->delete();

        // Store user identifier (email or phone) for reset form
        $identifier = $user->email ?: $user->phone;

        return redirect()->route('password.reset', ['token' => $token])
            ->with('email', $user->email ?: $user->phone)
            ->with('success', __('website.otp_verified_proceed_reset'));
    }

    public function showResetForm($token)
    {
        return view('website.auth.reset-password', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|string', // Can be email or phone
            'password' => 'required|min:6|confirmed',
        ]);

        // Determine if identifier is email or phone
        $identifier = $request->email;
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
        
        if ($isEmail) {
            // Reset with email
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();
                    event(new PasswordReset($user));
                }
            );
        } else {
            // Reset with phone - find user by phone
            $phone = preg_replace('/[^0-9]/', '', $identifier);
            $user = User::where('phone', $phone)
                ->where('is_admin', 0)
                ->first();
            
            if (!$user) {
                return back()->withErrors(['email' => [__('website.user_not_found_with_phone')]]);
            }
            
            // Verify token - check both standard and custom tokens
            $emailIdentifier = 'phone_' . $user->phone . '@reset.local';
            $tokenRecord = DB::table('password_reset_tokens')
                ->where('email', $emailIdentifier)
                ->first();
            
            $tokenValid = false;
            if ($tokenRecord && Hash::check($request->token, $tokenRecord->token)) {
                $tokenValid = true;
            } elseif ($user->email && Password::tokenExists($user, $request->token)) {
                $tokenValid = true;
            }
            
            if (!$tokenValid) {
                return back()->withErrors(['email' => [__('passwords.token')]]);
            }
            
            // Reset password
            $user->forceFill([
                'password' => Hash::make($request->password),
                'remember_token' => Str::random(60),
            ])->save();
            
            // Delete token
            if ($tokenRecord) {
                DB::table('password_reset_tokens')->where('email', $emailIdentifier)->delete();
            } else {
                Password::deleteToken($user);
            }
            
            event(new PasswordReset($user));
            
            $status = Password::PASSWORD_RESET;
        }

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('website.login')->with('status', __('passwords.reset'))
            : back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user exists with this email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // User exists - login
                // Check if user has google_id, if not update it
                if (!$user->google_id) {
                    $user->google_id = $googleUser->getId();
                    $user->save();
                }

                // Check if email is verified, if not verify it
                if (!$user->email_verified_at) {
                    $user->email_verified_at = now();
                    $user->status = 'active';
                    $user->save();
                }

                Auth::guard('web')->login($user, true);
                $request = request();
                $request->session()->regenerate();

                return redirect()->intended(route('website.home'));
            } else {
                // User doesn't exist - create new account
                DB::beginTransaction();

                try {
                    $user = User::create([
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'google_id' => $googleUser->getId(),
                        'is_admin' => 0,
                        'status' => 'active',
                        'email_verified_at' => now(), // Google emails are verified
                        'password' => Hash::make(Str::random(32)), // Random password since they use Google
                    ]);

                    // Update avatar if available
                    if ($googleUser->getAvatar()) {
                        // You can download and store the avatar using Spatie Media Library if needed
                    }

                    DB::commit();

                    event(new Registered($user));

                    Auth::guard('web')->login($user, true);
                    $request = request();
                    $request->session()->regenerate();

                    return redirect()->intended(route('website.home'))
                        ->with('success', __('website.account_created_successfully'));

                } catch (\Throwable $e) {
                    DB::rollBack();
                    report($e);

                    return redirect()->route('website.register')
                        ->withErrors(['register' => __('website.account_creation_error')]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Google OAuth Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('website.login')
                ->withErrors(['email' => __('website.google_login_failed')]);
        }
    }

}
