<?php

namespace App\DataTables;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class OrdersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Order> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('customer', function ($order) {
                if ($order->user_id && $order->user) {
                    $url = $order->user->getFirstMediaUrl('users');
                    $img = $url
                        ? '<img height="30" width="30" style="object-fit:cover; border-radius:50%; margin-right:8px;" src="' . $url . '" alt="user image">'
                        : '<span class="avatar-placeholder me-2">👤</span>';
                    return '<div class="d-flex align-items-center">'
                        . $img .
                        '<span>' . e($order->user->name ?? 'N/A') . '</span>
                    </div>';
                } else {
                    return '<div class="d-flex align-items-center">
                        <span class="me-2">👤</span>
                        <span>' . e($order->guest_name ?? 'Guest') . '</span>
                    </div>';
                }
            })
            ->addColumn('contact', function ($order) {
                if ($order->user_id && $order->user) {
                    return e($order->user->phone ?? '-');
                } else {
                    return e($order->guest_phone ?? '-');
                }
            })
            ->addColumn('branch_name', function ($order) {
                return $order->branch ? e($order->branch->name) : '-';
            })
            ->addColumn('payment_info', function ($order) {
                $paymentMethod = ucfirst($order->payment_method ?? 'Cash');
                $paymentStatus = $order->payment_status ?? 'pending';
                $statusColors = [
                    'paid' => 'success',
                    'pending' => 'warning',
                    'failed' => 'danger'
                ];
                $statusColor = $statusColors[$paymentStatus] ?? 'secondary';
                
                $html = '<div>
                    <small>' . $paymentMethod . '</small><br>
                    <span class="badge bg-label-' . $statusColor . '">' . ucfirst($paymentStatus) . '</span>
                </div>';
                
                if ($order->wallet_amount > 0) {
                    $html .= '<small class="text-info">Wallet: ' . number_format($order->wallet_amount, 3) . '</small>';
                }
                
                return $html;
            })
            ->addColumn('status', function ($order) {
                $statuses = [
                    'pending' => 'warning',
                    'processing' => 'info',
                    'completed' => 'success',
                    'cancelled' => 'danger'
                ];
                $badgeColor = $statuses[$order->status] ?? 'secondary';
                return '<span class="badge bg-label-' . $badgeColor . '">' . __('admin.' . $order->status) . '</span>';
            })
            ->addColumn('order_info', function ($order) {
                $type = $order->order_type === 'delivery' ? __('admin.delivery') : __('admin.pickup');
                $mealType = $order->meal_type ?? 'N/A';
                return '<div>
                    <strong>#' . e($order->order_number) . '</strong><br>
                    <small>' . $type . ' - ' . $mealType . '</small>
                </div>';
            })
            ->addColumn('products_count', function ($order) {
                return $order->items()->count();
            })
            ->editColumn('total', function ($order) {
                $currency = \App\Models\Currency::getCurrentCurrencySign() ?? 'SR';
                $html = '<div>
                    <strong>' . number_format($order->total, 3) . ' ' . $currency . '</strong>';
                
                if ($order->discount_amount > 0) {
                    $html .= '<br><small class="text-danger">Discount: -' . number_format($order->discount_amount, 3) . ' ' . $currency . '</small>';
                    if ($order->coupon_code) {
                        $html .= '<br><small class="text-muted">(' . e($order->coupon_code) . ')</small>';
                    }
                }
                
                $html .= '</div>';
                return $html;
            })
            ->editColumn('created_at', function ($order) {
                return $order->created_at->format('Y-m-d H:i');
            })
            ->addColumn('action', function ($order) {
                $editUrl = route('orders.edit', $order->id);
                $statusUrl = route('orders.update-status', $order->id);
                $viewUrl = route('orders.show', $order->id);
                
                $statusOptions = '';
                foreach (['pending', 'processing', 'completed', 'cancelled'] as $status) {
                    $selected = $order->status === $status ? 'selected' : '';
                    $statusOptions .= '<option value="' . $status . '" ' . $selected . '>' . __('admin.' . $status) . '</option>';
                }

                return '
                    <div class="dropdown">
                        <button class="btn btn-sm btn-default" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="icon-base ti tabler-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="' . $viewUrl . '" class="dropdown-item">
                                    <i class="icon-base ti tabler-eye"></i> ' . __("admin.view") . '
                                </a>
                            </li>
                            <li>
                                <a href="' . $editUrl . '" class="dropdown-item">
                                    <i class="icon-base ti tabler-edit"></i> ' . __("admin.edit") . '
                                </a>
                            </li>
                            <li class="dropdown-divider"></li>
                            <li>
                                <h6 class="dropdown-header">' . __("admin.change_status") . '</h6>
                            </li>
                            <li>
                                <select class="form-select form-select-sm ms-2 me-2 status-select" data-id="' . $order->id . '" data-url="' . $statusUrl . '">
                                    ' . $statusOptions . '
                                </select>
                            </li>
                        </ul>
                    </div>';
            })
            ->rawColumns(['action', 'customer', 'status', 'order_info', 'payment_info', 'total'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Order>
     */
    public function query(Order $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['user', 'items', 'branch', 'coupon']);
        
        // Filter by user_id if provided
        $userId = request()->get('user_id');
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        // Filter by type if provided
        $type = request()->get('type', 'all');
        
        if ($type && $type !== 'all') {
            switch ($type) {
                case 'pending_orders':
                    $query->where('status', 'pending');
                    break;
                case 'shipping_orders':
                    $query->where('status', 'processing');
                    break;
                case 'canceled_orders':
                    $query->where('status', 'cancelled');
                    break;
                case 'completed_orders':
                    $query->where('status', 'completed');
                    break;
            }
        }
        
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $type = request()->get('type', 'all');
        $userId = request()->get('user_id');
        
        // Build AJAX URL with parameters
        $ajaxUrl = url()->current();
        if ($type && $type !== 'all') {
            $ajaxUrl .= '?type=' . $type;
        }
        if ($userId) {
            $ajaxUrl .= ($type && $type !== 'all' ? '&' : '?') . 'user_id=' . $userId;
        }
        
        return $this->builder()
            ->setTableId('orders-table')
            ->columns($this->getColumns())
            ->minifiedAjax($ajaxUrl)
            ->orderBy(1, 'desc')
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            ])->parameters([
                'language' => $this->getDataTableLanguage()
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')
                ->title('#')
                ->addClass('text-start')
                ->orderable(false)
                ->searchable(false),
            Column::make('order_info')->title(__('admin.order_number'))->addClass('text-start')->name('order_number'),
            Column::make('customer')->title(__('admin.customer'))->addClass('text-start')->orderable(false),
            Column::computed('contact')->title(__('admin.phone'))->addClass('text-start')->orderable(false),
            Column::computed('branch_name')->title(__('admin.branches'))->addClass('text-start')->orderable(false),
            Column::make('products_count')->title(__('admin.products_count'))->addClass('text-start')->searchable(false)->orderable(false),
            Column::make('total')->title(__('admin.total'))->addClass('text-start'),
            Column::computed('payment_info')->title(__('admin.payment'))->addClass('text-start')->orderable(false),
            Column::make('status')->title(__('admin.status'))->addClass('text-start'),
            Column::make('created_at')->title(__('admin.date'))->addClass('text-start'),
            Column::computed('action')->title(__('admin.action'))
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-start'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Orders_' . date('Y-m-d');
    }

    protected function getDataTableLanguage(): array
    {
        $locale = app()->getLocale();

        $languages = [
            'en' => [
                'processing' => 'Processing...',
                'search' => 'Search:',
                'lengthMenu' => 'Show _MENU_ entries',
                'info' => 'Showing _START_ to _END_ of _TOTAL_ entries',
                'infoEmpty' => 'Showing 0 to 0 of 0 entries',
                'infoFiltered' => '(filtered from _MAX_ total entries)',
                'loadingRecords' => 'Loading...',
                'zeroRecords' => 'No matching records found',
                'emptyTable' => 'No data available in table',
                'paginate' => [
                    'first' => 'First',
                    'previous' => 'Previous',
                    'next' => 'Next',
                    'last' => 'Last',
                ],
            ],
            'ar' => [
                'processing' => 'جارٍ المعالجة...',
                'search' => 'بحث:',
                'lengthMenu' => 'عرض _MENU_ سجلات',
                'info' => 'عرض _START_ إلى _END_ من أصل _TOTAL_ سجلات',
                'infoEmpty' => 'عرض 0 إلى 0 من أصل 0 سجلات',
                'infoFiltered' => '(تمت التصفية من أصل _MAX_ سجلات)',
                'loadingRecords' => 'جارٍ التحميل...',
                'zeroRecords' => 'لم يتم العثور على سجلات مطابقة',
                'emptyTable' => 'لا توجد بيانات متاحة في الجدول',
                'paginate' => [
                    'first' => 'الأول',
                    'previous' => 'السابق',
                    'next' => 'التالي',
                    'last' => 'الأخير',
                ],
            ],
        ];

        return $languages[$locale] ?? $languages['en'];
    }
}

