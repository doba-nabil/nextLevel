<?php

namespace App\DataTables;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BannerDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Banner> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $locale = app()->getLocale();
        
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('status', function ($q) {
                $checked = $q->active ? 'checked' : '';
                $toggleUrl = route('banners.toggle-active', $q->id);
                return '<div class="form-check form-switch">
                    <input class="form-check-input status-toggle" type="checkbox" 
                           data-id="' . $q->id . '" 
                           data-url="' . $toggleUrl . '"
                           ' . $checked . '>
                </div>';
            })
            ->addColumn('image_ar', function ($banner) {
                $url = $banner->getFirstMediaUrl('banner_image_ar');
                return $url
                    ? '<img src="' . $url . '" alt="Arabic banner" style="height:50px;width:auto;object-fit:contain;border-radius:4px;max-width:150px;">'
                    : '<span class="text-muted">' . __('admin.no_image') . '</span>';
            })
            ->addColumn('image_en', function ($banner) {
                $url = $banner->getFirstMediaUrl('banner_image_en');
                return $url
                    ? '<img src="' . $url . '" alt="English banner" style="height:50px;width:auto;object-fit:contain;border-radius:4px;max-width:150px;">'
                    : '<span class="text-muted">' . __('admin.no_image') . '</span>';
            })
            ->addColumn('order', function ($banner) {
                return $banner->order ?? 0;
            })
            ->addColumn('action', function ($banner) {
                $editUrl = url('/admin/banners/' . $banner->id . '/edit');
                $deleteUrl = url('/admin/banners/' . $banner->id);

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
                        data-id="' . $banner->id . '"
                        data-url="' . $deleteUrl . '"
                        data-table=".table"
                        title="' . __("admin.delete") . '">
                        <i class="icon-base ti tabler-trash"></i> ' . __("admin.delete") . '
                    </a>
                </li>
            </ul>
        </div>';
            })
            ->rawColumns(['image_ar', 'image_en', 'action', 'status'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Banner>
     */
    public function query(Banner $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('banner-table')
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
            Column::make('DT_RowIndex')
                ->title('#')
                ->addClass('text-start')
                ->orderable(false)
                ->searchable(false),
            Column::make('image_ar')->title(__('admin.arabic') . ' ' . __('admin.image'))->addClass('text-start')->orderable(false)->searchable(false),
            Column::make('image_en')->title(__('admin.english') . ' ' . __('admin.image'))->addClass('text-start')->orderable(false)->searchable(false),
            Column::make('link')->title(__('admin.link'))->addClass('text-start'),
            Column::make('order')->title(__('admin.order'))->addClass('text-start'),
            Column::computed('status')->title(__('admin.status'))->addClass('text-start'),
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
        return 'Banner ' . date('Y-m-d');
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

