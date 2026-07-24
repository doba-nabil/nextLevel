<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\OrdersDataTable;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request, OrdersDataTable $dataTable)
    {
        $type = $request->get('type', 'all');
        return $dataTable->with('type', $type)->render('dashboard.orders', compact('type'));
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
            'coupon'
        ])->findOrFail($id);
        return view('dashboard.orders.show', compact('order'));
    }

    public function edit($id)
    {
        $order = Order::with([
            'user', 
            'items.product', 
            'items.addons.addon',
            'items.children.product',
            'branch',
            'coupon'
        ])->findOrFail($id);
        return view('dashboard.orders.edit', compact('order'));
    }

    public function update(Request $request, $id)
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
            if ($request->has('guest_name')) $data['guest_name'] = $request->guest_name;
            if ($request->has('guest_email')) $data['guest_email'] = $request->guest_email;
            if ($request->has('guest_phone')) $data['guest_phone'] = $request->guest_phone;
            if ($request->has('guest_address')) $data['guest_address'] = $request->guest_address;
        }

        $order->update($data);

        return redirect()->route('orders.show', $order->id)->with('success', __('admin.update_success'));
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

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
            'coupon'
        ])->findOrFail($id);
        
        return view('dashboard.orders.invoice', compact('order'));
    }
}













