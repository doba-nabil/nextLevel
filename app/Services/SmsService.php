<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SmsService
{
    private $username;
    private $password;
    private $baseUrl;

    public function __construct()
    {
        $this->username = config('kwtsms.username', env('KWTSMS_USERNAME', 'run2diet'));
        $this->password = config('kwtsms.password', env('KWTSMS_PASSWORD'));
        $this->baseUrl = config('kwtsms.base_url', env('KWTSMS_BASE_URL', 'https://www.kwtsms.com/API'));
        
        if (empty($this->password)) {
            throw new \Exception('KWTSMS_PASSWORD is not configured in .env file. Please add KWTSMS_PASSWORD=sQjs-VCNa6 to your .env file.');
        }
    }

    /**
     * Send SMS message
     * 
     * @param string $phoneNumber Phone number (with country code, e.g., 965XXXXXXXX)
     * @param string $message Message to send
     * @return array
     */
    public function sendSms($phoneNumber, $message)
    {
        try {
            // Clean phone number - remove any non-numeric characters
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            
            // Ensure phone number starts with country code (965 for Kuwait)
            if (!str_starts_with($phoneNumber, '965')) {
                // If it starts with 0, replace with 965
                if (str_starts_with($phoneNumber, '0')) {
                    $phoneNumber = '965' . substr($phoneNumber, 1);
                } else {
                    // If it doesn't start with country code, add it
                    $phoneNumber = '965' . $phoneNumber;
                }
            }

            $url = $this->baseUrl . '/send/';
            $params = [
                'username' => $this->username,
                'password' => $this->password,
                'mobile' => $phoneNumber,
                'message' => $message,
                'lang' => config('kwtsms.language', '3'), // 3 = Arabic + English
                'sender' => config('kwtsms.sender', env('KWTSMS_SENDER', 'Run2Diet')),
            ];

            Log::info('Sending SMS', [
                'phone' => $phoneNumber,
                'url' => $url,
            ]);

            $response = Http::timeout(30)
                ->asForm()
                ->post($url, $params);

            $result = $response->body();
            
            Log::info('SMS API Response', [
                'status' => $response->status(),
                'response' => $result,
                'phone' => $phoneNumber,
            ]);

            // Parse response (kwtsms typically returns JSON or plain text)
            $responseData = json_decode($result, true);
            
            // Check for kwtsms specific error codes (ERR### format)
            if (preg_match('/ERR\d{3}:\s*(.+)/i', $result, $matches)) {
                $errorCode = strtoupper(trim(explode(':', $result)[0]));
                $errorMessage = $this->getKwtsmsErrorMessage($errorCode, $matches[1] ?? $result);
                
                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'response' => $result,
                    'error_code' => $errorCode,
                    'error_type' => 'kwtsms_error'
                ];
            }

            // Check for common error patterns in response
            $errorMessages = [
                'error' => 'Error',
                'failed' => 'Failed',
                'invalid' => 'Invalid',
                'unauthorized' => 'Unauthorized',
                'insufficient' => 'Insufficient',
                'balance' => 'Balance',
                'wrong' => 'Wrong',
                'denied' => 'Denied',
                'blocked' => 'Blocked',
            ];

            $resultLower = strtolower($result);
            foreach ($errorMessages as $key => $label) {
                if (stripos($resultLower, $key) !== false) {
                    $errorMessage = $this->extractErrorMessage($result, $responseData);
                    return [
                        'success' => false,
                        'message' => $errorMessage,
                        'response' => $result,
                        'error_type' => $key
                    ];
                }
            }
            
            if ($response->successful()) {
                // Check if response indicates success
                // Adjust based on actual API response format
                if (is_array($responseData) && isset($responseData['status']) && $responseData['status'] == 'success') {
                    return [
                        'success' => true,
                        'message' => 'SMS sent successfully',
                        'response' => $responseData
                    ];
                } elseif (stripos($result, 'success') !== false || stripos($result, 'sent') !== false || stripos($result, 'ok') !== false) {
                    return [
                        'success' => true,
                        'message' => 'SMS sent successfully',
                        'response' => $result
                    ];
                } else {
                    // Try to extract error message from response
                    $errorMessage = $this->extractErrorMessage($result, $responseData);
                    return [
                        'success' => false,
                        'message' => $errorMessage,
                        'response' => $result
                    ];
                }
            }

            // HTTP error status codes
            $statusCode = $response->status();
            $errorMessage = $this->getHttpErrorMessage($statusCode);
            
            return [
                'success' => false,
                'message' => $errorMessage,
                'response' => $result,
                'status_code' => $statusCode
            ];

        } catch (\Exception $e) {
            Log::error('SMS Service Error', [
                'phone' => $phoneNumber ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'SMS sending error: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send OTP code via SMS
     * 
     * @param string $phoneNumber Phone number
     * @param string $otpCode OTP code (6 digits)
     * @param string $type Type of OTP (registration, password_reset, phone_change)
     * @return array
     */
    public function sendOtp($phoneNumber, $otpCode, $type = 'verification')
    {
        $messages = [
            'registration' => __('website.sms_otp_registration', ['otp' => $otpCode]),
            'password_reset' => __('website.sms_otp_password_reset', ['otp' => $otpCode]),
            'phone_change' => __('website.sms_otp_phone_change', ['otp' => $otpCode]),
            'verification' => __('website.sms_otp_verification', ['otp' => $otpCode]),
        ];

        $message = $messages[$type] ?? __('website.sms_otp_verification', ['otp' => $otpCode]);
        
        // Fallback to English if translation not available
        if ($message === __('website.sms_otp_verification', ['otp' => $otpCode])) {
            $message = "Your verification code is: {$otpCode}";
        }

        return $this->sendSms($phoneNumber, $message);
    }

    /**
     * Get user-friendly error message for kwtsms error codes
     * 
     * @param string $errorCode Error code (e.g., ERR025)
     * @param string $originalMessage Original error message
     * @return string
     */
    private function getKwtsmsErrorMessage($errorCode, $originalMessage)
    {
        $errorMessages = [
            'ERR001' => __('website.sms_error_invalid_credentials'),
            'ERR002' => __('website.sms_error_account_disabled'),
            'ERR003' => __('website.sms_error_insufficient_balance'),
            'ERR004' => __('website.sms_error_invalid_sender'),
            'ERR005' => __('website.sms_error_invalid_message'),
            'ERR010' => __('website.sms_error_invalid_phone'),
            'ERR011' => __('website.sms_error_phone_blocked'),
            'ERR012' => __('website.sms_error_invalid_country_code'),
            'ERR020' => __('website.sms_error_rate_limit'),
            'ERR025' => __('website.sms_error_no_valid_numbers'),
            'ERR030' => __('website.sms_error_service_unavailable'),
            'ERR040' => __('website.sms_error_database_error'),
            'ERR050' => __('website.sms_error_network_error'),
        ];

        // Check if we have a translation for this error code
        if (isset($errorMessages[$errorCode])) {
            return $errorMessages[$errorCode];
        }

        // Return generic error with original message
        return __('website.sms_error_generic', ['code' => $errorCode, 'message' => trim($originalMessage)]);
    }

    /**
     * Extract error message from API response
     * 
     * @param string $result Raw response
     * @param array|null $responseData Parsed JSON response
     * @return string
     */
    private function extractErrorMessage($result, $responseData = null)
    {
        // If response is JSON array, try to extract message
        if (is_array($responseData)) {
            if (isset($responseData['message'])) {
                return $responseData['message'];
            }
            if (isset($responseData['error'])) {
                return $responseData['error'];
            }
            if (isset($responseData['msg'])) {
                return $responseData['msg'];
            }
            if (isset($responseData['description'])) {
                return $responseData['description'];
            }
        }

        // Try to extract meaningful error from plain text
        $resultLower = strtolower($result);
        
        // Common error patterns
        if (stripos($resultLower, 'insufficient balance') !== false || stripos($resultLower, 'low balance') !== false) {
            return __('website.sms_insufficient_balance');
        }
        if (stripos($resultLower, 'invalid username') !== false || stripos($resultLower, 'wrong username') !== false) {
            return __('website.sms_invalid_credentials');
        }
        if (stripos($resultLower, 'invalid password') !== false || stripos($resultLower, 'wrong password') !== false) {
            return __('website.sms_invalid_credentials');
        }
        if (stripos($resultLower, 'invalid mobile') !== false || stripos($resultLower, 'wrong mobile') !== false) {
            return __('website.sms_invalid_phone');
        }
        if (stripos($resultLower, 'unauthorized') !== false || stripos($resultLower, 'access denied') !== false) {
            return __('website.sms_unauthorized');
        }
        if (stripos($resultLower, 'blocked') !== false) {
            return __('website.sms_phone_blocked');
        }
        if (stripos($resultLower, 'timeout') !== false) {
            return __('website.sms_timeout');
        }

        // Return generic error with first 100 characters of response
        $errorPreview = strlen($result) > 100 ? substr($result, 0, 100) . '...' : $result;
        return __('website.sms_sending_failed') . ': ' . $errorPreview;
    }

    /**
     * Get HTTP error message based on status code
     * 
     * @param int $statusCode
     * @return string
     */
    private function getHttpErrorMessage($statusCode)
    {
        $messages = [
            400 => __('website.sms_bad_request'),
            401 => __('website.sms_unauthorized'),
            403 => __('website.sms_forbidden'),
            404 => __('website.sms_not_found'),
            429 => __('website.sms_rate_limit'),
            500 => __('website.sms_server_error'),
            502 => __('website.sms_bad_gateway'),
            503 => __('website.sms_service_unavailable'),
            504 => __('website.sms_gateway_timeout'),
        ];

        return $messages[$statusCode] ?? __('website.sms_api_error', ['code' => $statusCode]);
    }

    /**
     * Check SMS balance
     * 
     * @return array
     */
    public function checkBalance()
    {
        try {
            $url = $this->baseUrl . '/balance/';
            $params = [
                'username' => $this->username,
                'password' => $this->password,
            ];

            $response = Http::timeout(30)
                ->asForm()
                ->post($url, $params);

            $result = $response->body();
            
            return [
                'success' => $response->successful(),
                'response' => $result,
                'data' => json_decode($result, true)
            ];

        } catch (\Exception $e) {
            Log::error('SMS Balance Check Error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Balance check error: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
}

