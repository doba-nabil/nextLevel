<?php

namespace App\DataTables;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BranchProductsDataTable extends DataTable
{
    protected $branchId;

    public function __construct($branchId = null)
    {
        $this->branchId = $branchId;
    }

    public function setBranchId($branchId)
    {
        $this->branchId = $branchId;
        return $this;
    }

    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Product> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('name', function ($product) {
                return $product->getTranslation('name', app()->getLocale());
            })
            ->addColumn('image', function ($product) {
                $imageUrl = $product->getFirstMediaUrl('products', 'thumb');
                if ($imageUrl) {
                    return '<img src="' . $imageUrl . '" alt="' . htmlspecialchars($product->getTranslation('name', app()->getLocale())) . '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">';
                }
                return '<div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                    <i class="icon-base ti tabler-image" style="font-size: 20px; color: #999;"></i>
                </div>';
            })
            ->addColumn('category', function ($product) {
                if ($product->category) {
                    return $product->category->getTranslation('name', app()->getLocale());
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('status', function ($product) {
                $branchPivot = $product->branches->first();
                $status = $branchPivot ? ($branchPivot->pivot->status ?? 'available') : 'available';

                if ($status === 'available') {
                    return '<span class="badge bg-label-success">' . __('admin.available') . '</span>';
                } else {
                    return '<span class="badge bg-label-danger">' . __('admin.unavailable') . '</span>';
                }
            })
            ->addColumn('action', function ($product) {
                $branchPivot = $product->branches->first();
                $currentStatus = $branchPivot ? ($branchPivot->pivot->status ?? 'available') : 'available';
                $btnClass = $currentStatus === 'available' ? 'btn-danger' : 'btn-success';
                $btnIcon = $currentStatus === 'available' ? 'tabler-eye-off' : 'tabler-eye';
                $btnText = $currentStatus === 'available' ? __('admin.hide') : __('admin.show');

                return '<button type="button"
                        class="btn btn-sm toggle-status-btn ' . $btnClass . '"
                        data-product-id="' . $product->id . '"
                        data-branch-id="' . $this->branchId . '"
                        data-current-status="' . $currentStatus . '">
                    <i class="icon-base ti ' . $btnIcon . '"></i>
                    <span class="toggle-text">' . $btnText . '</span>
                </button>';
            })
            ->rawColumns(['image', 'status', 'action', 'category'])
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
     * @return QueryBuilder<Product>
     */
    public function query(Product $model): QueryBuilder
    {
        $branchId = $this->branchId ?? request()->route('id');

        return $model->newQuery()
            ->whereHas('branches', function($q) use ($branchId) {
                $q->where('branches.id', $branchId);
            })
            ->with(['branches' => function($q) use ($branchId) {
                $q->where('branches.id', $branchId);
            }])
            ->with('category');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('branch-products-table')
            ->columns($this->getColumns())
            ->minifiedAjax(url()->current())
            ->orderBy(1)
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
            Column::make('image')
                ->title(__('admin.image'))
                ->addClass('text-start')
                ->orderable(false)
                ->searchable(false),
            Column::make('name')
                ->title(__('admin.name'))
                ->addClass('text-start'),
            Column::make('category')
                ->title(__('admin.category'))
                ->addClass('text-start')
                ->orderable(false)
                ->searchable(false),
            Column::computed('status')
                ->title(__('admin.status'))
                ->addClass('text-start')
                ->orderable(false)
                ->searchable(false),
            Column::computed('action')
                ->title(__('admin.action'))
                ->exportable(false)
                ->printable(false)
                ->width(150)
                ->addClass('text-start'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Branch Products ' . date('Y-m-d');
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
