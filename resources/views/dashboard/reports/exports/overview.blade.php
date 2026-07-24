<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('admin.reports_overview') }}</title>
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
        <h1>{{ __('admin.reports_overview') }}</h1>
        <p class="info">{{ __('admin.date_range') }}: {{ $dateRange['start']->format('Y-m-d') }} - {{ $dateRange['end']->format('Y-m-d') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>{{ __('admin.order_number') }}</th>
                <th>{{ __('admin.customer') }}</th>
                <th>{{ __('admin.branch') }}</th>
                <th>{{ __('admin.status') }}</th>
                <th>{{ __('admin.payment_method') }}</th>
                <th>{{ __('admin.total') }}</th>
                <th>{{ __('admin.date') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row['order_number'] }}</td>
                    <td>{{ $row['customer'] }}</td>
                    <td>{{ $row['branch'] }}</td>
                    <td>{{ $row['status'] }}</td>
                    <td>{{ $row['payment_method'] }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['date'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
