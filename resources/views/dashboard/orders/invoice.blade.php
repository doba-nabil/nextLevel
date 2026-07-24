<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('admin.invoice') }} - {{ $order->order_number }}</title>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 20px;
            }
            .print-button {
                display: none !important;
            }
        }
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
        }
        .invoice-header {
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .invoice-header h1 {
            margin: 0;
            color: #333;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-box {
            flex: 1;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            margin: 0 10px;
        }
        .info-box h3 {
            margin-top: 0;
            color: #333;
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .info-box p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background: #333;
            color: #fff;
            padding: 12px;
            text-align: left;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        table tr:last-child td {
            border-bottom: none;
        }
        .total-section {
            margin-top: 20px;
            text-align: right;
        }
        .total-row {
            display: flex;
            justify-content: flex-end;
            padding: 8px 0;
        }
        .total-row.label {
            font-weight: bold;
            color: #333;
        }
        .total-row.final {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
        }
        .print-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin: 20px 0;
        }
        .print-button:hover {
            background: #0056b3;
        }
        .invoice-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success { background: #28a745; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-info { background: #17a2b8; color: white; }
        @media print {
            @page {
                margin: 1cm;
            }
        }
        [dir="rtl"] {
            direction: rtl;
            text-align: right;
        }
        [dir="rtl"] .info-box {
            text-align: right;
        }
        [dir="rtl"] .total-section {
            text-align: left;
        }
        [dir="rtl"] table th,
        [dir="rtl"] table td {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" class="print-button">
            <i class="fa fa-print"></i> {{ __('admin.print') }}
        </button>
        <a href="{{ route('orders.show', $order->id) }}" style="margin-left: 10px; padding: 12px 24px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;">
            <i class="fa fa-arrow-left"></i> {{ __('admin.back') }}
        </a>
    </div>

    <div class="invoice-header">
        <h1>{{ __('admin.invoice') }}</h1>
        <p style="margin: 5px 0; color: #666;">{{ __('admin.invoice_number') }}: <strong>{{ $order->order_number }}</strong></p>
        <p style="margin: 5px 0; color: #666;">{{ __('admin.date') }}: {{ $order->created_at->format('Y-m-d H:i') }}</p>
    </div>

    <div class="invoice-info">
        <div class="info-box">
            <h3>{{ __('admin.customer') }} {{ __('admin.information') }}</h3>
            @if($order->user_id && $order->user)
                <p><strong>{{ $order->user->name }}</strong></p>
                <p>{{ __('admin.email') }}: {{ $order->user->email }}</p>
                @if($order->user->phone)
                    <p>{{ __('admin.phone') }}: {{ $order->user->phone }}</p>
                @endif
                @if($order->user->address)
                    <p>{{ __('admin.address') }}: {{ $order->user->address }}</p>
                @endif
            @else
                <p><strong>{{ $order->guest_name ?? 'Guest' }}</strong></p>
                @if($order->guest_email)
                    <p>{{ __('admin.email') }}: {{ $order->guest_email }}</p>
                @endif
                @if($order->guest_phone)
                    <p>{{ __('admin.phone') }}: {{ $order->guest_phone }}</p>
                @endif
            @endif
        </div>
        <div class="info-box">
            <h3>{{ __('admin.order') }} {{ __('admin.information') }}</h3>
            <p><strong>{{ __('admin.status') }}:</strong> 
                <span class="badge badge-{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : ($order->status === 'processing' ? 'info' : 'warning')) }}">
                    {{ __('admin.' . $order->status) }}
                </span>
            </p>
            <p><strong>{{ __('admin.order_type') }}:</strong> 
                {{ $order->order_type === 'delivery' ? __('admin.delivery') : __('admin.pickup') }}
            </p>
            @if($order->branch)
                <p><strong>{{ __('admin.branches') }}:</strong> {{ $order->branch->name }}</p>
            @endif
            @if($order->meal_type)
                <p><strong>{{ __('admin.meal_type') }}:</strong> 
                    @if($order->meal_type === 'asap')
                        {{ __('admin.as_soon_as_possible') ?? 'ASAP' }}
                    @elseif($order->meal_type === 'scheduled')
                        {{ __('admin.scheduled') }}
                    @elseif($order->meal_type === 'dine_in')
                        {{ __('admin.dine_in') }}
                    @else
                        {{ __('admin.as_soon_as_possible') ?? 'ASAP' }}
                    @endif
                </p>
            @endif
            @if($order->scheduled_date && $order->meal_type === 'scheduled')
                <p><strong>{{ __('admin.scheduled_date') ?? 'Scheduled Date' }}:</strong> {{ $order->scheduled_date }}</p>
            @endif
            @if($order->scheduled_time && $order->meal_type === 'scheduled')
                <p><strong>{{ __('admin.scheduled_time') ?? 'Scheduled Time' }}:</strong> {{ $order->scheduled_time }}</p>
            @endif
            @if($order->payment_method)
                <p><strong>{{ __('admin.payment_method') }}:</strong> {{ ucfirst($order->payment_method) }}</p>
            @endif
            @if($order->payment_status)
                <p><strong>{{ __('admin.payment_status') }}:</strong> 
                    <span class="badge badge-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'warning') }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </p>
            @endif
            @if($order->payment_id)
                <p><strong>{{ __('admin.payment_id') }}:</strong> {{ $order->payment_id }}</p>
            @endif
        </div>
    </div>

    @if($order->order_type === 'delivery')
        <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
            <h4 style="margin-top: 0;">{{ __('admin.delivery_address') }}</h4>
            @if($order->guest_address)
                <p><strong>{{ __('admin.address') }}:</strong> {{ $order->guest_address }}</p>
            @endif
            @if($order->user_id && $order->user && $order->user->addresses)
                @php
                    $selectedAddress = $order->user->addresses()->where('is_main', 1)->first();
                    if (!$selectedAddress && $order->address_id) {
                        $selectedAddress = $order->user->addresses()->find($order->address_id);
                    }
                @endphp
                @if($selectedAddress)
                    <p><strong>{{ __('admin.address') }}:</strong> {{ $selectedAddress->full_address }}</p>
                    @if($selectedAddress->additional_directions)
                        <p style="font-size: 12px; color: #666;">
                            <strong>{{ __('admin.additional_directions') }}:</strong> {{ $selectedAddress->additional_directions }}
                        </p>
                    @endif
                @endif
            @endif
            @if($order->lat && $order->long)
                <p style="font-size: 12px; color: #666;">
                    <strong>{{ __('admin.coordinates') }}:</strong> {{ $order->lat }}, {{ $order->long }}
                </p>
            @endif
        </div>
    @endif

    @if($order->order_type === 'pick_up' && $order->branch)
        <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
            <h4 style="margin-top: 0;">{{ __('admin.pickup_branch') ?? 'Pickup Branch' }}</h4>
            <p><strong>{{ __('admin.name') }}:</strong> {{ $order->branch->name }}</p>
            @if($order->branch->address)
                <p><strong>{{ __('admin.address') }}:</strong> {{ $order->branch->address }}</p>
            @endif
            @if($order->branch->phone)
                <p><strong>{{ __('admin.phone') }}:</strong> {{ $order->branch->phone }}</p>
            @endif
            @if($order->branch->whatsapp)
                <p><strong>{{ __('admin.whatsapp') }}:</strong> {{ $order->branch->whatsapp }}</p>
            @endif
        </div>
    @endif

    @if($order->order_notes)
        <div style="margin-top: 20px; padding: 15px; background: #fffbf0; border-radius: 5px; border-left: 4px solid #ffc107;">
            <h4 style="margin-top: 0;">{{ __('admin.order_notes') ?? 'Order Notes' }}</h4>
            <p style="margin: 0; white-space: pre-wrap;">{{ $order->order_notes }}</p>
        </div>
    @endif

    <h3 style="margin-top: 30px; margin-bottom: 15px;">{{ __('admin.order_items') }}</h3>
    <table>
        <thead>
            <tr>
                <th>{{ __('admin.product') }}</th>
                <th>{{ __('admin.quantity') }}</th>
                <th>{{ __('admin.price') }}</th>
                <th>{{ __('admin.total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->product->name ?? 'N/A' }}</strong>
                        @if($item->addons && $item->addons->count() > 0)
                            <div style="font-size: 11px; color: #666; margin-top: 5px;">
                                <strong>+ {{ __('admin.additionals') ?? 'Options' }}:</strong>
                                @foreach($item->addons as $addon)
                                    <div style="margin-left: 10px;">• {{ $addon->addon->name ?? 'N/A' }}</div>
                                @endforeach
                            </div>
                        @endif
                        @if($item->children && $item->children->count() > 0)
                            <div style="font-size: 11px; color: #666; margin-top: 5px;">
                                <strong>→ {{ __('admin.box_items') ?? 'Box Items' }}:</strong>
                                @foreach($item->children as $child)
                                    <div style="margin-left: 10px;">
                                        <strong>{{ $child->product->name ?? 'N/A' }}</strong>
                                        @if($child->addons && $child->addons->count() > 0)
                                            <div style="margin-left: 15px;">
                                                @foreach($child->addons as $childAddon)
                                                    <div>• {{ $childAddon->addon->name ?? 'N/A' }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if($item->notes)
                            <div style="font-size: 11px; color: #ff9800; margin-top: 5px; font-style: italic;">
                                <strong>{{ __('admin.notes') ?? 'Notes' }}:</strong> {{ $item->notes }}
                            </div>
                        @endif
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</td>
                    <td><strong>{{ number_format($item->total, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        @php
            // Calculate subtotal from items
            $itemsSubtotal = $order->items->sum('total');
        @endphp
        <div class="total-row">
            <span style="margin-right: 20px;">{{ __('admin.subtotal') }}:</span>
            <span>{{ number_format($itemsSubtotal, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>
        </div>
        @if($order->delivery_cost > 0)
            <div class="total-row">
                <span style="margin-right: 20px;">{{ __('admin.delivery_cost') }}:</span>
                <span>+{{ number_format($order->delivery_cost, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>
            </div>
        @endif
        @if($order->discount_amount > 0)
            <div class="total-row" style="color: #dc3545;">
                <span style="margin-right: 20px;">{{ __('admin.discount') }}:</span>
                <span>-{{ number_format($order->discount_amount, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>
                @if($order->coupon_code)
                    <span style="margin-left: 10px; font-size: 12px;">({{ $order->coupon_code }})</span>
                @endif
            </div>
        @endif
        @if($order->wallet_amount > 0)
            <div class="total-row" style="color: #17a2b8;">
                <span style="margin-right: 20px;">{{ __('admin.from_wallet') }}:</span>
                <span>-{{ number_format($order->wallet_amount, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>
            </div>
        @endif
        @if($order->gateway_amount > 0)
            <div class="total-row" style="color: #28a745;">
                <span style="margin-right: 20px;">{{ __('admin.from_gateway') }}:</span>
                <span>{{ number_format($order->gateway_amount, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>
            </div>
        @endif
        <div class="total-row final">
            <span style="margin-right: 20px;">{{ __('admin.total') }}:</span>
            <span>{{ number_format($order->total, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</span>
        </div>
    </div>

    @if($order->coupon)
        <div style="margin-top: 30px; padding: 15px; background: #e8f5e9; border-radius: 5px; border-left: 4px solid #28a745;">
            <h4 style="margin-top: 0;">{{ __('admin.coupon') }} {{ __('admin.information') }}</h4>
            <p><strong>{{ __('admin.code') }}:</strong> {{ $order->coupon_code }}</p>
            @if($order->coupon->name)
                <p><strong>{{ __('admin.name') }}:</strong> {{ $order->coupon->name }}</p>
            @endif
            <p><strong>{{ __('admin.discount') }}:</strong> 
                @if($order->coupon->type === 'percent')
                    {{ $order->coupon->value }}%
                @else
                    {{ number_format($order->coupon->value, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}
                @endif
            </p>
        </div>
    @endif

    <div class="invoice-footer">
        <p>{{ __('admin.thank_you') }}</p>
        <p style="margin-top: 10px; font-size: 11px;">
            {{ __('admin.invoice_generated_at') }}: {{ now()->format('Y-m-d H:i:s') }}
        </p>
    </div>

    <script>
     
    </script>
</body>
</html>

