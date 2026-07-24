<?php

namespace App\DataTables;

use App\Models\AddonGroup;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AddonTypeDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<AddonGroup> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function ($branch) {
                return $branch->getTranslation('name', app()->getLocale());
            })
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
            ->addColumn('action', function ($branch) {
                $editUrl = route('addon_groups.edit', $branch->id);
                $deleteUrl = route('addon_groups.destroy', $branch->id);

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
                        data-id="' . $branch->id . '"
                        data-url="' . $deleteUrl . '"
                        data-table="#branch-table"
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
                $keyword = trim($keyword);
                if (empty($keyword)) {
                    return;
                }
                $keywordLower = mb_strtolower($keyword, 'UTF-8');
                $query->where(function($q) use ($keywordLower) {
                    $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"en\"'))) LIKE ?", ["%{$keywordLower}%"])
                      ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"ar\"'))) LIKE ?", ["%{$keywordLower}%"]);
                });
            });
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<ProductDefinition>
     */
    public function query(AddonGroup $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('branch-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
            ])
            ->parameters([
                'language' => $this->getDataTableLanguage(),
                'responsive' => true,
                'autoWidth' => false
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
            Column::make('max_items')->title(__('admin.max_items'))->addClass('text-start'),
            Column::computed('status')->title(__('admin.status'))->addClass('text-start'),
            Column::computed('action')->title(__('admin.action'))
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->addClass('text-start'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Addons Type ' . date('Y-m-d');
    }

    /**
     * Get the DataTable language settings.
     */
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
