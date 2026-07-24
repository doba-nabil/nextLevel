<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MyFatoorahService
{
    public $apiKey;
    public $baseUrl;
    public $isTest;

    public function __construct()
    {
        // Get API key directly from .env to ensure it's not using config default
        $this->apiKey = env('MYFATOORAH_API_KEY');
        $this->isTest = config('myfatoorah.test_mode', true);
        $countryCode = config('myfatoorah.country_mode', 'KWT');
        
        // Validate API key exists in .env
        if (empty($this->apiKey) || $this->apiKey === null || trim($this->apiKey) === '') {
            throw new \Exception('MyFatoorah API key is not configured. Please set MYFATOORAH_API_KEY in your .env file. The API key must be obtained from your MyFatoorah dashboard.');
        }
        
        // Trim any whitespace from API key
        $this->apiKey = trim($this->apiKey);
        
        // Warn if API key is suspiciously long (might be default/expired key)
        if (strlen($this->apiKey) > 500) {
            Log::warning('MyFatoorah API Key Warning', [
                'api_key_length' => strlen($this->apiKey),
                'message' => 'API key is unusually long. Please verify you are using a valid API key from MyFatoorah dashboard, not a default/expired key.'
            ]);
        }
        
        // Set base URL based on test mode and country
        if ($this->isTest) {
            $this->baseUrl = config('myfatoorah.test_url', 'https://apitest.myfatoorah.com');
        } else {
            $this->baseUrl = $this->getProductionUrl($countryCode);
        }
        
        // Log configuration (without exposing full API key)
        Log::info('MyFatoorah Service Initialized', [
            'test_mode' => $this->isTest,
            'base_url' => $this->baseUrl,
            'country_mode' => $countryCode,
            'api_key_length' => strlen($this->apiKey),
            'api_key_prefix' => substr($this->apiKey, 0, 10) . '...',
            'api_key_suffix' => '...' . substr($this->apiKey, -10),
            'source' => 'Reading directly from .env file (MYFATOORAH_API_KEY)'
        ]);
    }

    private function getProductionUrl($countryCode)
    {
        $urls = [
            'KWT' => 'https://api.myfatoorah.com',
            'SAU' => 'https://api-sa.myfatoorah.com',
            'ARE' => 'https://api.myfatoorah.com',
            'QAT' => 'https://api.myfatoorah.com',
            'BHR' => 'https://api.myfatoorah.com',
            'OMN' => 'https://api.myfatoorah.com',
            'JOD' => 'https://api.myfatoorah.com',
            'EGY' => 'https://api.myfatoorah.com',
        ];

        return $urls[$countryCode] ?? 'https://api.myfatoorah.com';
    }

    public function createInvoice($data)
    {
        try {
            Log::info('MyFatoorah Invoice Creation Request', [
                'url' => $this->baseUrl . '/v2/SendPayment',
                'data' => $data
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/v2/SendPayment', $data);

            $responseBody = $response->body();
            $result = $response->json();
            
            // If JSON parsing fails, try to get the raw response
            if ($result === null && !empty($responseBody)) {
                // Try to decode manually
                $result = json_decode($responseBody, true);
                if ($result === null) {
                    // If still null, create a basic structure
                    $result = ['raw_response' => $responseBody];
                }
            }
            
            Log::info('MyFatoorah Invoice Creation Response', [
                'status' => $response->status(),
                'result' => $result,
                'raw_response' => $responseBody
            ]);

            if ($response->failed()) {
                $errorMessage = 'HTTP Error ' . $response->status();
                
                // Special handling for 401 Unauthorized
                if ($response->status() === 401) {
                    $errorMessage = 'Authentication failed (401 Unauthorized). ';
                    
                    // Add MyFatoorah's specific error message if available
                    if (isset($result['Message'])) {
                        $errorMessage .= 'MyFatoorah Error: ' . $result['Message'] . '. ';
                    } elseif (!empty($responseBody) && $responseBody !== 'null') {
                        // If no JSON message but we have a response body, include it
                        $errorMessage .= 'Response: ' . substr($responseBody, 0, 200) . '. ';
                    }
                    
                    // Add validation errors if available
                    if (isset($result['ValidationErrors']) && is_array($result['ValidationErrors'])) {
                        $errorMessage .= 'Validation Errors: ' . json_encode($result['ValidationErrors']) . '. ';
                    }
                    
                    // Add troubleshooting steps
                    $errorMessage .= 'Please verify: ';
                    $errorMessage .= '1) API key is active in MyFatoorah dashboard, ';
                    $errorMessage .= '2) API key matches test/production mode, ';
                    $errorMessage .= '3) API key has not expired. ';
                    $errorMessage .= 'Check logs for detailed error information.';
                    
                    // Log the full response for debugging
                    Log::error('MyFatoorah 401 Error Details', [
                        'full_response' => $result,
                        'raw_response_body' => $responseBody,
                        'response_status' => $response->status(),
                        'api_key_length' => strlen($this->apiKey ?? ''),
                        'api_key_first_20_chars' => substr($this->apiKey ?? '', 0, 20),
                        'api_key_last_20_chars' => substr($this->apiKey ?? '', -20),
                        'test_mode' => $this->isTest,
                        'base_url' => $this->baseUrl,
                        'request_url' => $this->baseUrl . '/v2/SendPayment',
                        'note' => 'API key length of 688 characters is unusually long. Typical MyFatoorah API keys are 200-300 characters. You may be using the default/expired key from config file.'
                    ]);
                } else {
                    if (isset($result['Message'])) {
                        $errorMessage .= ': ' . $result['Message'];
                    }
                    if (isset($result['ValidationErrors'])) {
                        $errorMessage .= ' - Validation: ' . json_encode($result['ValidationErrors']);
                    }
                }
                
                Log::error('MyFatoorah Invoice Creation Failed', [
                    'response' => $result,
                    'status' => $response->status(),
                    'api_key_length' => strlen($this->apiKey ?? ''),
                    'api_key_prefix' => substr($this->apiKey ?? '', 0, 10) . '...',
                    'test_mode' => $this->isTest,
                    'base_url' => $this->baseUrl,
                    'request_url' => $this->baseUrl . '/v2/SendPayment',
                    'headers_sent' => [
                        'Authorization' => 'Bearer ' . substr($this->apiKey ?? '', 0, 10) . '...',
                        'Content-Type' => 'application/json'
                    ]
                ]);
                
                throw new \Exception($errorMessage);
            }

            if (!isset($result['IsSuccess']) || !$result['IsSuccess']) {
                $errorMessage = $result['Message'] ?? 'Invoice creation failed';
                if (isset($result['ValidationErrors'])) {
                    $errorMessage .= ' - ' . json_encode($result['ValidationErrors']);
                }
                throw new \Exception($errorMessage);
            }

            if (!isset($result['Data']['InvoiceURL']) || !isset($result['Data']['InvoiceId'])) {
                throw new \Exception('Invoice URL or ID missing in response');
            }

            return [
                'success' => true,
                'invoiceId' => $result['Data']['InvoiceId'],
                'invoiceURL' => $result['Data']['InvoiceURL'],
                'data' => $result['Data']
            ];

        } catch (\Exception $e) {
            Log::error('MyFatoorah Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getPaymentStatus($paymentId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/v2/GetPaymentStatus', [
                'Key' => $paymentId,
                'KeyType' => 'PaymentId'
            ]);

            if ($response->failed()) {
                Log::error('MyFatoorah Payment Status Failed', [
                    'response' => $response->json()
                ]);
                throw new \Exception('Failed to get payment status');
            }

            $result = $response->json();
            
            if (!$result['IsSuccess']) {
                throw new \Exception($result['Message'] ?? 'Payment status check failed');
            }

            return [
                'success' => true,
                'status' => $result['Data']['InvoiceStatus'],
                'amount' => $result['Data']['InvoiceValue'],
                'data' => $result['Data']
            ];

        } catch (\Exception $e) {
            Log::error('MyFatoorah Payment Status Exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Test API connection and authentication
     * This can be used to verify if the API key is valid
     */
    public function testConnection()
    {
        try {
            // Use a simple endpoint to test authentication
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(10)->get($this->baseUrl . '/v2/InitiatePayment');

            $result = $response->json();
            
            Log::info('MyFatoorah Connection Test', [
                'status' => $response->status(),
                'success' => $response->successful(),
                'result' => $result
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'message' => $result['Message'] ?? 'Connection test completed',
                'data' => $result
            ];
        } catch (\Exception $e) {
            Log::error('MyFatoorah Connection Test Failed', [
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

