<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ArmadaWebhookController extends Controller
{
    public function handleDeliveryUpdate(Request $request)
    {
        $payload = $request->all();
        $headers = $request->headers->all();

        Log::channel('armada')->info('Armada Webhook Received', [
            'payload' => $payload,
            'headers' => $headers
        ]);
        $armadaId = $payload['code'] ?? null;

        if (!$armadaId) {
            return response()->json(['message' => 'Armada ID (code) missing'], 400);
        }
        $order = Order::where('armada_id', $armadaId)->first();

        if (!$order) {
            Log::channel('armada')->warning('Armada Webhook: Order not found', ['armada_id' => $armadaId]);
            return response()->json(['message' => 'Order not found'], 404);
        }
        $webhookKey = $request->header('order-webhook-key') ?? $request->header('Authorization');
        
        if ($webhookKey !== $order->armada_header) {
            Log::channel('armada')->warning('Armada Webhook: Unauthorized access attempt', [
                'order_id' => $order->id,
                'received_key' => $webhookKey,
                'stored_key' => $order->armada_header,
                'all_headers' => $headers // Log all headers for debugging
            ]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $armadaStatus = $payload['orderStatus'] ?? null;
        
        $order->delivery_info = $payload;
        $order->delivery_status = $armadaStatus;

        if (!empty($payload['trackingLink'])) {
            $order->armada_link = $payload['trackingLink'];
        }
        if (!empty($payload['qrCodeLink'])) {
            $order->armada_qr = $payload['qrCodeLink'];
        }

        if ($armadaStatus) {
            switch ($armadaStatus) {
                case 'new':
                case 'driver_assigned':
                case 'waiting_pack':
                    // Keep as processing
                    if ($order->status !== 'processing' && $order->status !== 'completed' && $order->status !== 'cancelled') {
                         $order->status = 'processing';
                    }
                    break;
                
                case 'picked_up':
                case 'en_route':
                    
                    if ($order->status !== 'completed' && $order->status !== 'cancelled') {
                        $order->status = 'processing'; 
                    }
                    break;

                case 'completed':
                    if ($order->status !== 'completed') {
                        $order->status = 'completed';
                    }
                    break;

                case 'cancelled':
                case 'expired':
                    if ($order->status !== 'cancelled') {
                        $order->status = 'cancelled';
                    }
                    break;
            }
        }

        $order->save();

        Log::channel('armada')->info('Armada Webhook Processed Successfully', [
            'order_id' => $order->id,
            'new_status' => $order->status,
            'delivery_status' => $order->delivery_status
        ]);

        return response()->json(['message' => 'Order updated successfully']);
    }
}
