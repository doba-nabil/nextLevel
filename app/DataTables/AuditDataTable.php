<?php

namespace App\DataTables;

use OwenIt\Auditing\Models\Audit;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AuditDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('auditable_type', function ($audit) {
                return class_basename($audit->auditable_type);
            })
            ->addColumn('user', function ($audit) {
                return $audit->user ? $audit->user->name : 'نظام';
            })
            ->addColumn('event', function ($audit) {
                return __('audit.' . $audit->event, ['entity' => class_basename($audit->auditable_type)]);
            })
            ->addColumn('action', function ($audit) {
                $showUrl = route('audits.show', $audit->id);
                return '<a href="' . $showUrl . '" class="btn btn-sm btn-primary" title="عرض">
                    <i class="icon-base ti tabler-eye"></i>
                </a>';
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(Audit $model): QueryBuilder
    {
        return $model->newQuery()->with('user');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('audit-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            ])
            ->parameters([
                'language' => $this->getDataTableLanguage(),
                'responsive' => true,
                'autoWidth' => false
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('#')->addClass('text-start'),
            Column::make('auditable_type')->title(__('admin.model'))->addClass('text-start'),
            Column::make('user')->title(__('admin.user'))->addClass('text-start'),
            Column::make('event')->title(__('admin.event'))->addClass('text-start'),
            Column::make('created_at')->title(__('admin.date'))->addClass('text-start'),
            Column::computed('action')->title(__('admin.action'))
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->addClass('text-start'),
        ];
    }

    protected function filename(): string
    {
        return 'Audit_' . date('YmdHis');
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
