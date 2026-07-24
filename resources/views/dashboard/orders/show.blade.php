@extends('dashboard.layout.master')
@section('title', __('admin.order_number') . ' #' . $order->order_number)

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>{{ __('admin.order_number') }}: #{{ $order->order_number }}</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('orders.invoice', $order->id) }}" target="_blank" class="btn btn-primary">
                                <i class="icon-base ti tabler-file-invoice"></i> {{ __('admin.invoice') }}
                            </a>
                            <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                                <i class="icon-base ti tabler-arrow-left"></i> {{ __('admin.back') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Order Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">{{ __('admin.customer') }} {{ __('admin.information') }}</h6>
                                @if($order->user_id && $order->user)
                                    <div class="mb-2">
                                        <strong>{{ __('admin.name') }}:</strong> {{ $order->user->name }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>{{ __('admin.email') }}:</strong> {{ $order->user->email }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>{{ __('admin.phone') }}:</strong> {{ $order->user->phone ?? '-' }}
                                    </div>
                                    @if($order->user->address)
                                        <div class="mb-2">
                                            <strong>{{ __('admin.address') }}:</strong> {{ $order->user->address }}
                                        </div>
                                    @endif
                                @else
                                    <div class="mb-2">
                                        <strong>{{ __('admin.name') }}:</strong> {{ $order->guest_name ?? 'Guest' }}
                                    </div>
                                    @if($order->guest_email)
                                        <div class="mb-2">
                                            <strong>{{ __('admin.email') }}:</strong> {{ $order->guest_email }}
                                        </div>
                                    @endif
                                    @if($order->guest_phone)
                                        <div class="mb-2">
                                            <strong>{{ __('admin.phone') }}:</strong> {{ $order->guest_phone }}
                                        </div>
                                    @endif
                                    @if($order->guest_address)
                                        <div class="mb-2">
                                            <strong>{{ __('admin.address') }}:</strong> {{ $order->guest_address }}
                                        </div>
                                    @endif
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">{{ __('admin.order') }} {{ __('admin.information') }}</h6>
                                <div class="mb-2">
                                    <strong>{{ __('admin.status') }}:</strong> 
                                    <span class="badge bg-label-{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : ($order->status === 'processing' ? 'info' : 'warning')) }}">
                                        {{ __('admin.' . $order->status) }}
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <strong>{{ __('admin.order_type') }}:</strong> 
                                    <span class="badge bg-{{ $order->order_type === 'delivery' ? 'primary' : 'info' }}">
                                        {{ $order->order_type === 'delivery' ? __('admin.delivery') : __('admin.pickup') }}
                                    </span>
                                </div>
                                @if($order->order_type === 'delivery' && $order->delivery_cost)
                                    <div class="mb-2">
                                        <strong>{{ __('admin.delivery_cost') }}:</strong> 
                                        {{ number_format($order->delivery_cost, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}
                                    </div>
                                @endif
                                @if($order->branch)
                                    <div class="mb-2">
                                        <strong>{{ __('admin.branches') }}:</strong> {{ $order->branch->name }}
                                    </div>
                                    @php
                                        $branchCity = $order->branch->cities->first();
                                        $branchState = $branchCity ? $branchCity->parent : ($order->branch->location && $order->branch->location->type === 'city' ? $order->branch->location->parent : ($order->branch->location && $order->branch->location->type === 'state' ? $order->branch->location : null));
                                    @endphp
                                    @if($branchCity)
                                        <div class="mb-2">
                                            <strong>{{ __('admin.city') }}:</strong> {{ $branchCity->getTranslation('name', app()->getLocale()) }}
                                        </div>
                                    @endif
                                    @if($branchState)
                                        <div class="mb-2">
                                            <strong>{{ __('admin.governorate') }}:</strong> {{ $branchState->getTranslation('name', app()->getLocale()) }}
                                        </div>
                                    @endif
                                @endif
                                @if($order->meal_type)
                                    <div class="mb-2">
                                        <strong>{{ __('admin.meal_type') }}:</strong> 
                                        @if($order->meal_type === 'asap')
                                            {{ __('admin.as_soon_as_possible') }}
                                        @elseif($order->meal_type === 'scheduled')
                                            {{ __('admin.scheduled') }}
                                        @elseif($order->meal_type === 'dine_in')
                                            {{ __('admin.dine_in') }}
                                        @else
                                            {{ $order->meal_type }}
                                        @endif
                                    </div>
                                @endif
                                @if($order->scheduled_date && $order->scheduled_time)
                                    <div class="mb-2">
                                        <strong>{{ __('admin.scheduled') }}:</strong> {{ $order->scheduled_date }} {{ $order->scheduled_time }}
                                    </div>
                                @endif
                                <div class="mb-2">
                                    <strong>{{ __('admin.date') }}:</strong> {{ $order->created_at->format('Y-m-d H:i') }}
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Order Items -->
                        <h6 class="text-primary mb-3">{{ __('admin.order_items') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('admin.product') }}</th>
                                        <th>{{ __('admin.quantity') }}</th>
                                        <th>{{ __('admin.price') }}</th>
                                        <th>{{ __('admin.total') }}</th>
                                        @if($order->items->contains(fn($item) => !empty($item->notes)))
                                            <th>{{ __('admin.notes') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($item->product && $item->product->getFirstMediaUrl('products'))
                                                        <img src="{{ $item->product->getFirstMediaUrl('products', 'thumb') }}" 
                                                             alt="{{ $item->product->name }}" 
                                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; margin-right: 10px;">
                                                    @endif
                                                    <div>
                                                        <strong>{{ $item->product->name ?? 'N/A' }}</strong>
                                                        @if($item->addons && $item->addons->count() > 0)
                                                            <div class="small text-muted mt-1">
                                                                @foreach($item->addons as $addon)
                                                                    <div>+ {{ $addon->addon->name ?? 'N/A' }}</div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        @if($item->children && $item->children->count() > 0)
                                                            <div class="small text-muted mt-1">
                                                                @foreach($item->children as $child)
                                                                    <div>→ {{ $child->product->name ?? 'N/A' }}</div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ number_format($item->price, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</td>
                                            <td><strong>{{ number_format($item->total, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</strong></td>
                                            @if($order->items->contains(fn($item) => !empty($item->notes)))
                                                <td>
                                                    @if(!empty($item->notes))
                                                        <small class="text-muted">{{ $item->notes }}</small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <hr>

                        <!-- Payment & Pricing -->
                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <div class="table-responsive">
                                    <table class="table">
                                        @php
                                            // Calculate subtotal (sum of all order items before discount and delivery)
                                            $subtotal = $order->items->sum('total');
                                        @endphp
                                        <tr>
                                            <td><strong>{{ __('admin.subtotal') }}:</strong></td>
                                            <td class="text-end">{{ number_format($subtotal, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</td>
                                        </tr>
                                        @if($order->discount_amount > 0)
                                            <tr>
                                                <td><strong>{{ __('admin.discount') }}:</strong> 
                                                    @if($order->coupon_code)
                                                        <span class="badge bg-danger">{{ $order->coupon_code }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-end text-danger">-{{ number_format($order->discount_amount, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</td>
                                            </tr>
                                        @endif
                                        @if($order->order_type === 'delivery' && $order->delivery_cost > 0)
                                            <tr>
                                                <td><strong>{{ __('admin.delivery_cost') }}:</strong></td>
                                                <td class="text-end">{{ number_format($order->delivery_cost, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</td>
                                            </tr>
                                        @endif
                                        @if($order->wallet_amount > 0)
                                            <tr>
                                                <td><strong>{{ __('admin.from_wallet') }}:</strong></td>
                                                <td class="text-end text-info">-{{ number_format($order->wallet_amount, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</td>
                                            </tr>
                                        @endif
                                        @if($order->gateway_amount > 0)
                                            <tr>
                                                <td><strong>{{ __('admin.from_gateway') }}:</strong></td>
                                                <td class="text-end text-primary">{{ number_format($order->gateway_amount, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</td>
                                            </tr>
                                        @endif
                                        <tr class="table-active">
                                            <td><strong>{{ __('admin.total') }}:</strong></td>
                                            <td class="text-end"><strong>{{ number_format($order->total, 3) }} {{ \App\Models\Currency::getCurrentCurrencySign() }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ __('admin.payment_method') }}:</strong></td>
                                            <td class="text-end">{{ ucfirst($order->payment_method ?? 'Cash') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ __('admin.payment_status') }}:</strong></td>
                                            <td class="text-end">
                                                <span class="badge bg-label-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($order->payment_status ?? 'pending') }}
                                                </span>
                                            </td>
                                        </tr>
                                        @if($order->payment_id)
                                            <tr>
                                                <td><strong>{{ __('admin.payment_id') }}:</strong></td>
                                                <td class="text-end">
                                                    <small class="text-muted">{{ $order->payment_id }}</small>
                                                </td>
                                            </tr>
                                        @endif
                                        @if($order->coupon)
                                            <tr>
                                                <td><strong>{{ __('admin.coupon') }}:</strong></td>
                                                <td class="text-end">
                                                    <span class="badge bg-danger">{{ $order->coupon_code ?? $order->coupon->code ?? 'N/A' }}</span>
                                                    @if($order->coupon->name)
                                                        <br><small class="text-muted">{{ $order->coupon->name }}</small>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if($order->branch)
                            <hr>
                            <h6 class="text-primary mb-3">{{ __('admin.branch') ?? 'Branch' }} {{ __('admin.information') }}</h6>
                            <div class="mb-2">
                                <strong>{{ __('admin.name') }}:</strong> {{ $order->branch->name }}
                            </div>
                            @if($order->branch->address)
                                <div class="mb-2">
                                    <strong>{{ __('admin.address') }}:</strong> {{ $order->branch->address }}
                                </div>
                            @endif
                            @if($order->branch->phone)
                                <div class="mb-2">
                                    <strong>{{ __('admin.phone') }}:</strong> {{ $order->branch->phone }}
                                </div>
                            @endif
                            @if($order->branch->whatsapp)
                                <div class="mb-2">
                                    <strong>{{ __('admin.whatsapp') ?? 'WhatsApp' }}:</strong> {{ $order->branch->whatsapp }}
                                </div>
                            @endif
                        @endif

                        @if($order->order_type === 'delivery' && ($order->armada_id || $order->armada_link || $order->armada_qr))
                            <hr>
                            <h6 class="text-primary mb-3">{{ __('admin.armada_data') }}</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    @if($order->armada_id)
                                        <div class="mb-3">
                                            <strong>{{ __('admin.armada_id') }}:</strong>
                                            <span class="badge bg-primary ms-2">{{ $order->armada_id }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($order->armada_header)
                                        <div class="mb-3">
                                            <strong>{{ __('admin.armada_header') }}:</strong>
                                            <div class="mt-1">
                                                <code class="bg-white p-2 rounded d-inline-block">{{ $order->armada_header }}</code>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if($order->armada_link)
                                        <div class="mb-3">
                                            <strong>{{ __('admin.armada_tracking_link') }}:</strong>
                                            <div class="mt-2">
                                                <a href="{{ $order->armada_link }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="icon-base ti tabler-external-link"></i> {{ __('admin.view_tracking') }}
                                                </a>
                                                <small class="text-muted d-block mt-1">{{ Str::limit($order->armada_link, 80) }}</small>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if($order->armada_qr)
                                        <div class="mb-3">
                                            <strong>{{ __('admin.armada_qr_code') }}:</strong>
                                            <div class="mt-2">
                                                <img src="{{ $order->armada_qr }}" alt="Armada QR Code" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                                <div class="mt-2">
                                                    <a href="{{ $order->armada_qr }}" target="_blank" class="btn btn-sm btn-outline-secondary" download>
                                                        <i class="icon-base ti tabler-download"></i> {{ __('admin.download_qr') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @elseif($order->order_type === 'delivery' && !$order->armada_id)
                            <hr>
                            <div class="alert alert-info">
                                <i class="icon-base ti tabler-info-circle"></i> {{ __('admin.no_armada_data') }}
                            </div>
                        @endif

                        @if($order->order_type === 'delivery')
                            <hr>
                            <h6 class="text-primary mb-3">{{ __('admin.delivery_address') }}</h6>
                            @if($order->guest_address)
                                <div class="mb-2">
                                    <strong>{{ __('admin.address') }}:</strong>
                                    <p class="mb-0">{{ $order->guest_address }}</p>
                                </div>
                            @endif
                            @if($order->user_id && $order->user && $order->user->addresses)
                                @php
                                    $selectedAddress = $order->user->addresses()->where('is_main', 1)->first();
                                    if (!$selectedAddress && $order->address_id) {
                                        $selectedAddress = $order->user->addresses()->find($order->address_id);
                                    }
                                @endphp
                                @if($selectedAddress)
                                    <div class="mb-2">
                                        <strong>{{ __('admin.address') }}:</strong>
                                        <p class="mb-0">{{ $selectedAddress->full_address }}</p>
                                        @if($selectedAddress->additional_directions)
                                            <small class="text-muted d-block mt-1">
                                                <strong>{{ __('admin.additional_directions') ?? 'Additional Directions' }}:</strong> {{ $selectedAddress->additional_directions }}
                                            </small>
                                        @endif
                                    </div>
                                @endif
                            @endif
                            @if($order->lat && $order->long)
                                <div class="mb-2">
                                    <strong>{{ __('admin.coordinates') }}:</strong>
                                    <span class="text-muted">{{ $order->lat }}, {{ $order->long }}</span>
                                    <a href="https://www.google.com/maps?q={{ $order->lat }},{{ $order->long }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                        <i class="icon-base ti tabler-map-pin"></i> {{ __('admin.view_on_map') }}
                                    </a>
                                </div>
                            @endif
                        @endif

                        @if($order->order_type === 'pick_up' && $order->branch)
                            <hr>
                            <h6 class="text-primary mb-3">{{ __('admin.pickup_branch') ?? 'Pickup Branch' }}</h6>
                            <div class="mb-2">
                                <strong>{{ __('admin.name') }}:</strong> {{ $order->branch->name }}
                            </div>
                            @if($order->branch->address)
                                <div class="mb-2">
                                    <strong>{{ __('admin.address') }}:</strong> {{ $order->branch->address }}
                                </div>
                            @endif
                            @if($order->branch->phone)
                                <div class="mb-2">
                                    <strong>{{ __('admin.phone') }}:</strong> {{ $order->branch->phone }}
                                </div>
                            @endif
                            @if($order->scheduled_date && $order->scheduled_time)
                                <div class="mb-2">
                                    <strong>{{ __('admin.scheduled_pickup') ?? 'Scheduled Pickup' }}:</strong>
                                    {{ \Carbon\Carbon::parse($order->scheduled_date)->format('d M Y') }} 
                                    at {{ \Carbon\Carbon::parse($order->scheduled_time)->format('h:i A') }}
                                </div>
                            @endif
                        @endif

                        @if($order->order_notes)
                            <hr>
                            <h6 class="text-primary mb-3">{{ __('admin.order_notes') }}</h6>
                            <div class="alert alert-info">
                                <p class="mb-0">{{ $order->order_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('dashboard-head')
    @include('dashboard.partials.index.css')
@endsection

@section('dashboard-footer')
    @include('dashboard.partials.index.js')
@endsection

