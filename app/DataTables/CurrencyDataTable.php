<?php

namespace App\DataTables;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class CurrencyDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Currency> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function ($currency) {
                return $currency->name;
            })
            ->addColumn('country', fn($currency) => $currency->location ? $currency->location->name : '-')
            ->addColumn('action', function ($currency) {
                $editUrl = url('/admin-panel/currencies/' . $currency->id);
                $deleteUrl = url('/admin-panel/currencies/' . $currency->id);
                return '
                <a href="' . $editUrl . '/edit" class="btn btn-sm text-success" title="Edit">
                    <i class="icon-base ti tabler-edit"></i>
                </a>
                <button class="btn btn-sm text-danger delete-btn"
                        data-id="' . $currency->id . '"
                        data-url="' . $deleteUrl . '"
                        data-table="#currency-table"
                        title="Delete">
                    <i class="icon-base ti tabler-trash"></i>
                </button>
            ';
            })
            ->rawColumns(['action'])
            ->setRowId('id')
            ->filterColumn('name', function($query, $keyword) {
                $keyword = trim($keyword);
                if (empty($keyword)) {
                    return;
                }
                $keywordLower = mb_strtolower($keyword, 'UTF-8');
                $query->where(function($q) use ($keywordLower) {
                    $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"en\"'))) LIKE ?", ["%{$keywordLower}%"])
                      ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"ar\"'))) LIKE ?", ["%{$keywordLower}%"]);
                });
            })
            ->filterColumn('sign', function($query, $keyword) {
                $keyword = trim($keyword);
                if (empty($keyword)) {
                    return;
                }
                $keywordLower = mb_strtolower($keyword, 'UTF-8');
                $query->whereRaw('LOWER(sign) LIKE ?', ["%{$keywordLower}%"]);
            });
    }



    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Currency>
     */
    public function query(Currency $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('currency-table')
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
            Column::make('id')->title('#')->addClass('text-start'),
            Column::make('name')->title(__('admin.name'))->addClass('text-start'),
            Column::make('sign')->title(__('admin.sign'))->addClass('text-start'),
            Column::make('country')->title(__('admin.country'))->addClass('text-start'),
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
        return 'Currency ' . date('Y-m-d');
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
