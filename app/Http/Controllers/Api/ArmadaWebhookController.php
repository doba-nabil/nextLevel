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
        // 1. Verify Request (Optional but recommended: Check signature or token if Armada supports it)
        // Armada Documentation says: "Order-Webhook-Key" or similar might be used, but for now we trust the payload structure.
        
        $data = $request->all();
        
        Log::info('Armada Webhook Received', ['payload' => $data]);

        // 2. Extract Order Reference & Status
        // Payload structure usually contains 'reference' (our order_number) and 'status'
        // Example payload check required. Assuming based on standard delivery APIs.
        $orderNumber = $data['reference'] ?? null;
        $status = $data['status'] ?? null;
        // Armada Statuses map:
        // 'new', 'cancellation_offered', 'driver_assigned', 'picked_up', 'completed', 'cancelled', 'expired'

        if (!$orderNumber) {
            return response()->json(['message' => 'Reference missing'], 400);
        }

        // 3. Find Order
        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            Log::warning('Armada Webhook: Order not found', ['order_number' => $orderNumber]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        // 4. Update Order Status
        // Run2Diet Statuses: pending, processing, out_for_delivery, delivered, cancelled, etc.
        // We need to map Armada status to System status.
        
        /*
         * Mapping:
         * driver_assigned -> processing (or keep as is, potentially add 'driver_assigned' if system supports)
         * picked_up -> out_for_delivery
         * completed -> delivered
         * cancelled -> cancelled
         */

        switch ($status) {
            case 'driver_assigned':
                // Maybe update internal tracking info?
                // $order->update(['status' => 'processing']); 
                break;
            
            case 'picked_up':
                $order->update(['status' => 'out_for_delivery']);
                break;

            case 'completed':
                $order->update(['status' => 'delivered']);
                break;

            case 'cancelled':
            case 'expired':
                $order->update(['status' => 'cancelled']);
                break;
        }
        
        // Save raw status from Armada just in case
        // If there's a specific column for delivery_status, update it. Else, maybe log or store in meta.
        // For now, main status update is sufficient.

        return response()->json(['message' => 'Status updated']);
    }
}
