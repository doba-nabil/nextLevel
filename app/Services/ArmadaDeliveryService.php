<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ArmadaDeliveryService
{
    protected $baseUrl;
    protected $apiKey;
    protected $mode;

    public function __construct()
    {
        $this->mode = config('services.armada.mode', 0);

        $baseUrl = config('services.armada.url');
        if (!$baseUrl) {
            $baseUrl = 'https://api.armadadelivery.com/v0';
        }
        $this->baseUrl = $baseUrl;
    }

    /**
     * Calculate order amount with promo code and delivery cost
     * Based on the original Armada.php logic
     *
     * @param Order $order
     * @return float
     */
    private function calculateAmount(Order $order): float
    {
        $couponCode = $order->coupon_code;
        $couponAmount = $order->discount_amount ?? 0;
        $delivery = $order->delivery_cost ?? 0;

        $subtotal = $order->total - $delivery + $couponAmount;

        $couponType = null;
        if ($order->coupon) {
            $couponType = $order->coupon->type;
        } elseif ($couponCode && $couponAmount > 0) {
            if (abs($couponAmount - $delivery) < 0.01) {
                $couponType = 'free_delivery';
            } else {
                if ($couponAmount < $subtotal * 0.5) {
                    $couponType = 'percent';
                } else {
                    $couponType = 'fixed';
                }
            }
        }

        if ($couponCode && $couponType == 'fixed') {
            $amount = $subtotal - $couponAmount + $delivery;
        } elseif ($couponCode && $couponType == 'percent') {
            $amount = ($subtotal - $couponAmount) + $delivery;
        } elseif ($couponCode && $couponType == 'free_delivery') {
            $amount = $subtotal;
        } else {
            $amount = $subtotal + $delivery;
        }

        return max(0, (float) $amount);
    }

    /**
     * Parse address components from guest_address or user address
     *
     * @param Order $order
     * @return array
     */
    private function parseAddress(Order $order): array
    {
        $cityEn = '';
        $block = '';
        $street = '';
        $house = '';
        $flat = '';

        $userAddress = null;
        if ($order->address_id) {
            $userAddress = $order->address;
        } elseif ($order->user) {
            $userAddress = \App\Models\Address::where('user_id', $order->user->id)
                ->where('is_main', true)
                ->where('active', true)
                ->first();
        }

        if ($userAddress) {
            $block = $userAddress->block ?? '';
            $street = $userAddress->street ?? '';
            $house = $userAddress->building ?? '';
            $flat = $userAddress->apartment ?? '';
            $cityEn = $userAddress->area ?? $userAddress->city ?? '';
        }

        if (empty($block) && empty($street)) {
            $address = $order->guest_address ?? '';

            if (preg_match('/block[:\s]+(\d+)/i', $address, $matches)) {
                $block = $matches[1];
            }
            if (preg_match('/street[:\s]+([^,]+)/i', $address, $matches)) {
                $street = trim($matches[1]);
            }
            if (preg_match('/house[:\s]+([^,]+)/i', $address, $matches)) {
                $house = trim($matches[1]);
            }
            if (preg_match('/flat[:\s]+([^,]+)/i', $address, $matches)) {
                $flat = trim($matches[1]);
            }
        }

        if (empty($cityEn) && $order->branch && $order->branch->location_id) {
            $location = \App\Models\Location::find($order->branch->location_id);
            if ($location) {
                $cityEn = $location->getTranslation('name', 'en') ?? '';
            }
        }

        return [
            'city_en' => $cityEn,
            'block' => $block,
            'street' => $street,
            'house' => $house,
            'flat' => $flat,
        ];
    }

    /**
     * Create a delivery order in Armada
     *
     * @param Order $order
     * @return bool
     * @throws \Exception
     */
    public function createOrder(Order $order)
    {
        if (config('app.env') === 'local') {
            Log::channel('armada')->info('Armada Order Creation Skipped (Local Environment)', [
                'order_id' => $order->id
            ]);
            return true;
        }

        $apiKey = null;

        if ($order->branch && !empty($order->branch->armada_key)) {
            $apiKey = $order->branch->armada_key;
            Log::channel('armada')->info('Using Armada key from branch record', [
                'branch_id' => $order->branch->id,
                'api_url' => $this->baseUrl
            ]);
        } else {
            $apiKey = config('services.armada.fallback_key');
            if ($apiKey) {
                Log::channel('armada')->info('Using Armada fallback key from config', [
                    'branch_id' => $order->branch->id ?? null,
                    'api_url' => $this->baseUrl
                ]);
            }
        }

        if (!$apiKey) {
            $branchName = $order->branch ? $order->branch->getTranslation('name', 'en') : 'Unknown';
            $hasArmadaKey = $order->branch && !empty($order->branch->armada_key);
            Log::channel('armada')->warning('No Armada API Key found for branch: ' . $branchName . ' and no fallback key set.', [
                'branch_id' => $order->branch->id ?? null,
                'has_armada_key_field' => $hasArmadaKey,
                'api_url' => $this->baseUrl
            ]);
            throw new \Exception('Armada API Key is required but not found for this branch.');
        }

        $amount = $this->calculateAmount($order);

        $name = $order->user ? $order->user->name : $order->guest_name;
        $phone = $order->user ? $order->user->phone : $order->guest_phone;

        // Format phone number
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (!str_starts_with($phone, '+')) {
            if (!str_starts_with($phone, '965')) {
                if (str_starts_with($phone, '0')) {
                    $phone = '965' . substr($phone, 1);
                } else {
                    $phone = '965' . $phone;
                }
            }
            $phone = '+' . $phone;
        }

        $paymentType = ($order->payment_method == 'cash') ? 'cash' : 'paid';

        $webhook = uniqid();

        $addressParts = $this->parseAddress($order);

        $payload = [
            'platformName' => 'Application',
            'platformData' => [
                'orderId' => $order->id,
                'name' => $name,
                'phone' => $phone,
                'area' => $addressParts['city_en'],
                'block' => $addressParts['block'],
                'street' => $addressParts['street'],
                'buildingNumber' => $addressParts['house'],
                'apartment' => $addressParts['flat'],
                'instructions' => $order->order_notes ?? '',
                'amount' => $amount,
                'paymentType' => $paymentType,
            ],
        ];

        if ($order->lat && $order->long) {
            $payload['platformData']['latitude'] = (float) $order->lat;
            $payload['platformData']['longitude'] = (float) $order->long;
        }

        $url = $this->baseUrl . '/deliveries';

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . $apiKey,
            'Content-Type' => 'application/json',
            'order-webhook-key' => $webhook,
        ])->post($url, $payload);

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['code'])) {
                $order->armada_id = $data['code'];
                $order->armada_header = $webhook;
                $order->armada_link = $data['trackingLink'] ?? null;
                $order->armada_qr = $data['qrCodeLink'] ?? null;
                $order->save();

                Log::channel('armada')->info('Armada Order Created', [
                    'order_id' => $order->id,
                    'armada_id' => $data['code'],
                    'armada_response' => $data
                ]);

                return true;
            } else {
                Log::channel('armada')->error('Armada API Success but no code in response', [
                    'order_id' => $order->id,
                    'response' => $data
                ]);
                return false;
            }
        } else {
            Log::channel('armada')->error('Armada API Error', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $url,
                'mode' => $this->mode,
            ]);
            throw new \Exception('Armada API Error: ' . $response->body());
        }
    }

    /**
     * Cancel a delivery order in Armada
     *
     * @param Order $order
     * @return bool
     */
    public function cancelOrder(Order $order)
    {
        if (!$order->armada_id) {
            return false;
        }

        $url = $this->baseUrl . '/deliveries/' . $order->armada_id . '/cancel';

        $apiKey = null;
        if ($order->branch && !empty($order->branch->armada_key)) {
            $apiKey = $order->branch->armada_key;
        } else {
            $apiKey = config('services.armada.fallback_key');
        }

        if (!$apiKey) {
            Log::channel('armada')->warning('No Armada API Key found for cancellation', ['order_id' => $order->id]);
            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post($url);

        if ($response->successful()) {
            Log::channel('armada')->info('Armada Order Cancelled', [
                'order_id' => $order->id,
                'armada_id' => $order->armada_id
            ]);
            return true;
        } else {
            Log::channel('armada')->error('Armada Cancellation Failed', [
                'order_id' => $order->id,
                'armada_id' => $order->armada_id,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return false;
        }
    }
}
