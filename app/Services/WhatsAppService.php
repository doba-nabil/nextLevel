<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    private $apiKey;
    private $apiUrl;
    private $instanceId;
    private $phoneNumberId;

    public function __construct()
    {
        $this->apiKey = Setting::getValue('whatsapp_api_key', null, '');
        $this->phoneNumberId = Setting::getValue('whatsapp_phone_number_id', null, '');
        // Facebook WhatsApp Business API uses fixed URL
        $this->apiUrl = 'https://graph.facebook.com/v18.0';
    }

    /**
     * Send WhatsApp message
     *
     * @param string $phoneNumber Phone number (with country code, e.g., 965XXXXXXXX)
     * @param string $message Message to send
     * @return array
     */
    public function sendMessage($phoneNumber, $message)
    {
        try {
            // Check if API is configured
            if (empty($this->apiKey) || empty($this->phoneNumberId)) {
                Log::warning('WhatsApp API not configured. Skipping message send.', [
                    'phone' => $phoneNumber,
                    'has_api_key' => !empty($this->apiKey),
                    'has_phone_number_id' => !empty($this->phoneNumberId)
                ]);
                return [
                    'success' => false,
                    'message' => 'WhatsApp API not configured. Please set API Key and Phone Number ID in settings.'
                ];
            }

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

            // Build Facebook WhatsApp Business API URL
            $url = $this->apiUrl . '/' . $this->phoneNumberId . '/messages';

            // Prepare request data for Facebook WhatsApp Business API
            $data = [
                'messaging_product' => 'whatsapp',
                'to' => $phoneNumber,
                'type' => 'text',
                'text' => [
                    'body' => $message
                ]
            ];

            // Add Access Token as query parameter (Facebook API requirement)
            $url .= '?access_token=' . $this->apiKey;

            $headers = [
                'Content-Type' => 'application/json',
            ];

            Log::info('Sending WhatsApp message via Facebook API', [
                'phone' => $phoneNumber,
                'url' => str_replace($this->apiKey, '***HIDDEN***', $url),
                'phone_number_id' => $this->phoneNumberId,
            ]);

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($url, $data);

            $result = $response->json() ?? $response->body();

            Log::info('WhatsApp API Response', [
                'status' => $response->status(),
                'response' => $result,
                'phone' => $phoneNumber,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['messages']) && !empty($responseData['messages'])) {
                    return [
                        'success' => true,
                        'message' => 'WhatsApp message sent successfully',
                        'response' => $result,
                        'message_id' => $responseData['messages'][0]['id'] ?? null
                    ];
                }
            }

            // Handle error response
            $errorMessage = 'Failed to send WhatsApp message';
            if (is_array($result) && isset($result['error'])) {
                $errorMessage = $result['error']['message'] ?? $errorMessage;
            }

            return [
                'success' => false,
                'message' => $errorMessage,
                'response' => $result,
                'status_code' => $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp Service Error', [
                'phone' => $phoneNumber ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'WhatsApp sending error: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send order confirmation message
     *
     * @param \App\Models\Order $order
     * @return array
     */
    public function sendOrderConfirmation($order)
    {
        try {
            // Get customer phone number
            $phoneNumber = null;
            if ($order->user_id) {
                $phoneNumber = $order->user->phone ?? null;
            } else {
                $phoneNumber = $order->guest_phone ?? null;
            }

            if (empty($phoneNumber)) {
                Log::warning('No phone number found for order', [
                    'order_id' => $order->id
                ]);
                return [
                    'success' => false,
                    'message' => 'No phone number found for order'
                ];
            }

            // Build confirmation message
            $locale = app()->getLocale();
            $siteName = Setting::getValue('site_name', $locale, 'Run2Diet');

            $message = $this->buildOrderConfirmationMessage($order, $locale, $siteName);

            return $this->sendMessage($phoneNumber, $message);

        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp order confirmation', [
                'order_id' => $order->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send WhatsApp order confirmation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Build order confirmation message
     *
     * @param \App\Models\Order $order
     * @param string $locale
     * @param string $siteName
     * @return string
     */
    private function buildOrderConfirmationMessage($order, $locale, $siteName)
    {
        if ($locale === 'ar') {
            $message = " شكراً لك على طلبك!\n\n";
            $message .= "رقم الطلب: {$order->order_number}\n";
            $message .= "المبلغ الإجمالي: {$order->total} " . \App\Models\Currency::getCurrentCurrencySign() . "\n";
            if ($order->order_type === 'delivery') {
                $message .= "نوع الطلب: توصيل\n";
            } else {
                $message .= "نوع الطلب: استلام\n";
            }
            $message .= "\nسيتم تحديثك بحالة الطلب قريباً.\n";
            $message .= "شكراً لاختيارك {$siteName}";
        } else {
            $message = " Thank you for your order!\n\n";
            $message .= "Order Number: {$order->order_number}\n";
            $message .= "Total Amount: {$order->total} " . \App\Models\Currency::getCurrentCurrencySign() . "\n";
            if ($order->order_type === 'delivery') {
                $message .= "Order Type: Delivery\n";
            } else {
                $message .= "Order Type: Pickup\n";
            }
            $message .= "\nYou will be updated on your order status soon.\n";
            $message .= "Thank you for choosing {$siteName}";
        }

        return $message;
    }
}
