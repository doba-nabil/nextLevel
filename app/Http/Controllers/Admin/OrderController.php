<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\OrdersDataTable;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ArmadaDeliveryService;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:orders.index|orders,admin')->only('index');
        $this->middleware('permission:orders.edit|orders,admin')->only(['edit', 'update', 'updateStatus', 'sendToArmada']);
        $this->middleware('permission:orders.show|orders.index|orders,admin')->only(['show', 'invoice']);
        $this->middleware('permission:orders.delete|orders,admin')->only('destroy');
    }
    public function index(Request $request, OrdersDataTable $dataTable)
    {
        $type = $request->get('type', 'all');
        return $dataTable->with('type', $type)->render('dashboard.orders', compact('type'));
    }

    private function getSubProductsAndAddonsData($order)
    {
        $subProductIds = [];
        $addonIds = [];
        foreach ($order->items as $item) {
            if ($item->meta && isset($item->meta['subproducts'])) {
                foreach ($item->meta['subproducts'] as $sp) {
                    if (isset($sp['product_id'])) {
                        $subProductIds[] = $sp['product_id'];
                    }
                    if (isset($sp['addons']) && is_array($sp['addons'])) {
                        $addonIds = array_merge($addonIds, $sp['addons']);
                    }
                }
            }
        }
        
        $subProducts = [];
        if (!empty($subProductIds)) {
            $subProducts = \App\Models\Product::withoutGlobalScope('city_availability')
                ->whereIn('id', array_unique($subProductIds))
                ->pluck('name', 'id')
                ->toArray();
        }
        
        $subAddons = [];
        if (!empty($addonIds)) {
            $subAddons = \App\Models\Addon::where('active', 1)
                ->whereIn('id', array_unique($addonIds))
                ->pluck('name', 'id')
                ->toArray();
        }

        return ['subProducts' => $subProducts, 'subAddons' => $subAddons];
    }

    public function show($id)
    {
        $order = Order::with([
            'user.addresses',
            'items.product',
            'items.addons.addon',
            'items.children.product',
            'branch.cities.parent',
            'branch.location.parent',
            'coupon',
            'address'
        ])->findOrFail($id);
        $metaData = $this->getSubProductsAndAddonsData($order);
        $subProducts = $metaData['subProducts'];
        $subAddons = $metaData['subAddons'];
        return view('dashboard.orders.show', compact('order', 'subProducts', 'subAddons'));
    }

    public function edit($id)
    {
        $order = Order::with([
            'user',
            'items.product',
            'items.addons.addon',
            'items.children.product',
            'branch',
            'coupon',
            'address'
        ])->findOrFail($id);
        $metaData = $this->getSubProductsAndAddonsData($order);
        $subProducts = $metaData['subProducts'];
        $subAddons = $metaData['subAddons'];
        return view('dashboard.orders.edit', compact('order', 'subProducts', 'subAddons'));
    }

    public function update(Request $request, $id, ArmadaDeliveryService $armadaService)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
            'payment_status' => 'nullable|in:pending,paid,failed',
            'guest_name' => 'nullable|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'guest_address' => 'nullable|string',
        ]);

        $data = ['status' => $request->status];

        if ($request->has('payment_status')) {
            $data['payment_status'] = $request->payment_status;
        }

        // Update guest info if order is from guest
        if (!$order->user_id) {
            if ($request->has('guest_name'))
                $data['guest_name'] = $request->guest_name;
            if ($request->has('guest_email'))
                $data['guest_email'] = $request->guest_email;
            if ($request->has('guest_phone'))
                $data['guest_phone'] = $request->guest_phone;
            if ($request->has('guest_address'))
                $data['guest_address'] = $request->guest_address;
        }

        if ($request->status === 'cancelled' && $order->armada_id && $order->status !== 'cancelled') {
            try {
                $cancelled = $armadaService->cancelOrder($order);
                if ($cancelled) {
                    session()->flash('success', __('admin.armada_order_cancelled_successfully'));
                } else {
                    session()->flash('warning', __('admin.armada_order_cancellation_failed'));
                }
            } catch (\Exception $e) {
                session()->flash('error', __('admin.armada_order_cancellation_error') . ': ' . $e->getMessage());
            }
        }

        $order->update($data);

        return redirect()->route('orders.show', $order->id)->with('success', __('admin.update_success'));
    }

    public function updateStatus(Request $request, $id, ArmadaDeliveryService $armadaService)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        if ($request->status === 'cancelled' && $order->armada_id && $order->status !== 'cancelled') {
            try {
                $cancelled = $armadaService->cancelOrder($order);
                // For AJAX requests, we might want to return this info, 
                // but for now let's just log it or rely on the return message if we want to be fancy.
                // Since updateStatus returns JSON, we should probably add a message there.
            } catch (\Exception $e) {
                // Log error
                \Log::error('Error cancelling Armada order via updateStatus', ['error' => $e->getMessage()]);
            }
        }

        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => __('admin.status_updated_successfully'),
            'status' => $order->status
        ]);
    }

    public function invoice($id)
    {
        $order = Order::with([
            'user.addresses',
            'items.product',
            'items.addons.addon',
            'items.children.product',
            'items.children.addons.addon',
            'branch',
            'coupon',
            'address'
        ])->findOrFail($id);

        $metaData = $this->getSubProductsAndAddonsData($order);
        $subProducts = $metaData['subProducts'];
        $subAddons = $metaData['subAddons'];
        return view('dashboard.orders.invoice', compact('order', 'subProducts', 'subAddons'));
    }

    public function sendToArmada($id, \App\Services\ArmadaDeliveryService $armadaService)
    {
        $order = Order::findOrFail($id);

        if (strtolower($order->order_type) !== 'delivery') {
            return back()->with('error', __('admin.order_is_not_delivery'));
        }

        if ($order->status !== 'processing') {
            return back()->with('error', __('admin.order_status_must_be_processing') ?? 'Order status must be processing');
        }

        if ($order->armada_id) {
            return back()->with('error', __('admin.order_already_sent_to_armada'));
        }

        try {
            $success = $armadaService->createOrder($order);

            if ($success) {
                return back()->with('success', __('admin.sent_to_armada_successfully'));
            } else {
                return back()->with('error', __('admin.failed_to_send_to_armada'));
            }
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}













