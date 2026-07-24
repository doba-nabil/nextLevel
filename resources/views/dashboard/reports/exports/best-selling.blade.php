<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('admin.best_selling_products') }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header { text-align: center; margin-bottom: 20px; }
        .info { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('admin.best_selling_products') }}</h1>
        <p class="info">{{ __('admin.date_range') }}: {{ $dateRange['start']->format('Y-m-d') }} - {{ $dateRange['end']->format('Y-m-d') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('admin.product') }}</th>
                <th>{{ __('admin.category') }}</th>
                <th>{{ __('admin.total_quantity') }}</th>
                <th>{{ __('admin.total_revenue') }}</th>
                <th>{{ __('admin.order_count') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['product'] }}</td>
                    <td>{{ $row['category'] }}</td>
                    <td>{{ $row['total_quantity'] }}</td>
                    <td>{{ $row['total_revenue'] }}</td>
                    <td>{{ $row['order_count'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
