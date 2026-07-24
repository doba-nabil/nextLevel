<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('admin.best_branches') }}</title>
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
        <h1>{{ __('admin.best_branches') }}</h1>
        <p class="info">{{ __('admin.date_range') }}: {{ $dateRange['start']->format('Y-m-d') }} - {{ $dateRange['end']->format('Y-m-d') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('admin.branch') }}</th>
                <th>{{ __('admin.total_orders') }}</th>
                <th>{{ __('admin.total_revenue') }}</th>
                <th>{{ __('admin.avg_order_value') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['branch'] }}</td>
                    <td>{{ $row['total_orders'] }}</td>
                    <td>{{ $row['total_revenue'] }}</td>
                    <td>{{ $row['avg_order_value'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
