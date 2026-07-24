<?php

namespace App\DataTables;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CouponDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('status', function ($q) {
                switch ($q->active) {
                    case true:
                        return '<span class="badge bg-label-success">' . __('admin.active') . '</span>';
                    case false:
                        return '<span class="badge bg-label-secondary">' . __('admin.deactive') . '</span>';
                    default:
                        return '<span class="badge bg-label-info">' . $q->status . '</span>';
                }
            })
            ->addColumn('discount', function ($coupon) {
                if ($coupon->type === 'percent') {
                    return $coupon->value . ' %';
                } elseif ($coupon->type === 'fixed') {
                    return number_format($coupon->value, 3);
                }
                return $coupon->discount;
            })
            ->addColumn('discount_type', function ($coupon) {
                return $coupon->type === 'percent' ? __('admin.percent') : __('admin.fixed');
            })
            ->addColumn('user', function ($coupon) {
                return $coupon->user?->name ?? __('admin.all_users');
            })
            ->addColumn('action', function ($coupon) {
                $editUrl = route('coupons.edit', $coupon->id);
                $deleteUrl = route('coupons.destroy', $coupon->id);

                return '
                <div class="dropdown">
                    <button class="btn btn-sm btn-default" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon-base ti tabler-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="' . $editUrl . '" class="dropdown-item">
                                <i class="icon-base ti tabler-edit"></i> ' . __("admin.edit") . '
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="dropdown-item delete-btn"
                               data-id="' . $coupon->id . '"
                               data-url="' . $deleteUrl . '"
                               data-table="#coupon-table"
                               title="{{ __(\'admin.delete\') }}">
                               <i class="icon-base ti tabler-trash"></i> ' . __("admin.delete") . '
                            </a>
                        </li>
                    </ul>
                </div>';
            })
            ->rawColumns(['action', 'status'])
            ->setRowId('id')
            ->filterColumn('name', function ($query, $keyword) {
                $keyword = strtolower($keyword);
                $query->whereRaw('LOWER(code) LIKE ?', ["%{$keyword}%"]);
            });
    }

    public function query(Coupon $model): QueryBuilder
    {
        return $model->newQuery()->with('user');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('coupon-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload'),
            ])
            ->parameters([
                'language' => $this->getDataTableLanguage(),
                'responsive' => true,
                'autoWidth' => false,
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')
                ->title('#')
                ->addClass('text-start')
                ->orderable(false)
                ->searchable(false),
            Column::make('code')->title(__('admin.code'))->addClass('text-start'),
            Column::make('discount')->title(__('admin.discount'))->addClass('text-start'),
            Column::make('discount_type')->title(__('admin.discount_type'))->addClass('text-start'),
            Column::make('user')->title(__('admin.user'))->addClass('text-start'),
            Column::computed('status')->title(__('admin.status'))->addClass('text-start'),
            Column::computed('action')->title(__('admin.action'))
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->addClass('text-start'),
        ];
    }

    protected function filename(): string
    {
        return 'Coupons_' . date('YmdHis');
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
