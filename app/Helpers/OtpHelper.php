<?php

namespace App\Helpers;

class OtpHelper
{
    /**
     * Check if OTP should be shown in popup (test mode)
     * Controlled by .env OTP_TEST_MODE
     */
    public static function shouldShowOtpInPopup(): bool
    {
        // Use config() instead of env() to ensure cached values are used
        $value = config('app.otp_test_mode', env('OTP_TEST_MODE', false));
        
        // Handle string 'true', boolean true, or '1'
        $result = $value === true || strtolower((string)$value) === 'true' || $value === '1' || $value === 1;
        
        // Log for debugging
        \Log::info('OTP_TEST_MODE check', [
            'raw_value' => $value,
            'type' => gettype($value),
            'result' => $result,
            'env_value' => env('OTP_TEST_MODE')
        ]);
        
        return $result;
    }

    /**
     * Check if SMS should be skipped (test mode)
     * Controlled by .env OTP_TEST_MODE
     */
    public static function shouldSkipSms(): bool
    {
        // Use config() instead of env() to ensure cached values are used
        $value = config('app.otp_test_mode', env('OTP_TEST_MODE', false));
        
        // Handle string 'true', boolean true, or '1'
        return $value === true || strtolower((string)$value) === 'true' || $value === '1' || $value === 1;
    }

    /**
     * Get OTP response data for test mode
     */
    public static function getTestOtpResponse($otp, $message = null): array
    {
        return [
            'status' => true,
            'success' => true,
            'message' => $message ?? __('website.otp_sent_successfully') . ' (Testing Mode)',
            'otp' => $otp,
            'testing_mode' => true,
            'show_otp' => true,
            'sent_via' => 'test'
        ];
    }
}

