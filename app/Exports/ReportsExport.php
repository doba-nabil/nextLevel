<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $data;
    protected $type;

    public function __construct($data, $type = 'overview')
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        switch ($this->type) {
            case 'best-selling':
                return [
                    __('admin.product'),
                    __('admin.category'),
                    __('admin.total_quantity'),
                    __('admin.total_revenue'),
                    __('admin.order_count'),
                ];
            case 'best-branches':
                return [
                    __('admin.branch'),
                    __('admin.total_orders'),
                    __('admin.total_revenue'),
                    __('admin.avg_order_value'),
                ];
            case 'payment-methods':
                return [
                    __('admin.payment_method'),
                    __('admin.order_count'),
                    __('admin.total_revenue'),
                    __('admin.percentage') . ' %',
                ];
            case 'overview':
            default:
                return [
                    __('admin.order_number'),
                    __('admin.customer'),
                    __('admin.branch'),
                    __('admin.status'),
                    __('admin.payment_method'),
                    __('admin.total'),
                    __('admin.date'),
                ];
        }
    }

    public function map($row): array
    {
        switch ($this->type) {
            case 'best-selling':
                return [
                    $row['product'] ?? '',
                    $row['category'] ?? '',
                    $row['total_quantity'] ?? 0,
                    $row['total_revenue'] ?? '0.000',
                    $row['order_count'] ?? 0,
                ];
            case 'best-branches':
                return [
                    $row['branch'] ?? '',
                    $row['total_orders'] ?? 0,
                    $row['total_revenue'] ?? '0.000',
                    $row['avg_order_value'] ?? '0.000',
                ];
            case 'payment-methods':
                return [
                    $row['payment_method'] ?? '',
                    $row['count'] ?? 0,
                    $row['total_revenue'] ?? '0.000',
                    $row['percentage'] ?? 0,
                ];
            case 'overview':
            default:
                return [
                    $row['order_number'] ?? '',
                    $row['customer'] ?? '',
                    $row['branch'] ?? '',
                    $row['status'] ?? '',
                    $row['payment_method'] ?? '',
                    $row['total'] ?? '0.000',
                    $row['date'] ?? '',
                ];
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        $titles = [
            'overview' => __('admin.reports_overview'),
            'best-selling' => __('admin.best_selling_products'),
            'best-branches' => __('admin.best_branches'),
            'payment-methods' => __('admin.payment_methods_report'),
        ];

        return $titles[$this->type] ?? __('admin.reports');
    }
}
