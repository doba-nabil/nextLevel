<table class="table table-striped">
    <thead>
    <tr>
        <th>{{ __('admin.currency') }}</th>
        <th>{{ __('admin.before_discount') }}</th>
        <th>{{ __('admin.after_discount') }}</th>
        <th>{{ __('admin.discount_percentage') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($product->prices as $price)
        <tr>
            <td @if($price->has_discount) class="text-success" @endif>{{ $price->currency->name }}</td>
            <td @if($price->has_discount) class="text-success" @endif>{{ number_format($price->price, 3) }}</td>
            <td @if($price->has_discount) class="text-success" @endif>{{ number_format($price->discount_price, 3) }}</td>
            <td @if($price->has_discount) class="text-success" @endif>
                {{ $price->discount_percentage ? $price->discount_percentage.'%' : '-' }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
