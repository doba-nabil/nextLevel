<?php

namespace App\DataTables;

use App\Models\ProductNote;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductNoteDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<ProductNote> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('product', function ($note) {
                $product = $note->product;
                if (!$product) {
                    return '<span class="text-muted">' . __('admin.product_deleted') . '</span>';
                }
                $url = $product->getFirstMediaUrl('products');
                $image = $url
                    ? '<img src="' . $url . '" alt="product image" style="height:30px;width:30px;object-fit:contain;margin-right:5px;border-radius:4px;">'
                    : '';
                return $image . '<span>' . $product->name . '</span>';
            })
            ->addColumn('user', function ($note) {
                if ($note->user) {
                    return $note->user->name . '<br><small class="text-muted">' . $note->user->email . '</small>';
                }
                return '<span class="text-muted">' . __('admin.guest') . '</span>';
            })
            ->addColumn('note', function ($note) {
                $noteText = strlen($note->note) > 100 ? substr($note->note, 0, 100) . '...' : $note->note;
                return '<div style="max-width: 300px;">' . htmlspecialchars($noteText) . '</div>';
            })
            ->addColumn('date', function ($note) {
                return $note->created_at->format('Y-m-d H:i:s');
            })
            ->addColumn('action', function ($note) {
                $viewUrl = 'javascript:void(0)';
                $deleteUrl = url('/admin/product-notes/' . $note->id);

                return '
        <div class="dropdown">
            <button class="btn btn-sm btn-default" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="icon-base ti tabler-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a href="' . $viewUrl . '" class="dropdown-item view-note" data-id="' . $note->id . '"
                        title="' . __('admin.view') . '">
                        <i class="icon-base ti tabler-eye"></i> ' . __('admin.view') . '
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="dropdown-item delete-btn"
                        data-id="' . $note->id . '"
                        data-url="' . $deleteUrl . '"
                        data-table=".table"
                        title="' . __('admin.delete') . '">
                        <i class="icon-base ti tabler-trash"></i> ' . __('admin.delete') . '
                    </a>
                </li>
            </ul>
        </div>';
            })
            ->rawColumns(['product', 'user', 'note', 'action'])
            ->setRowId('id')
            ->filterColumn('product', function($query, $keyword) {
                $query->whereHas('product', function($q) use ($keyword) {
                    $keyword = trim($keyword);
                    if (empty($keyword)) {
                        return;
                    }
                    $keywordLower = mb_strtolower($keyword, 'UTF-8');
                    $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"en\"'))) LIKE ?", ["%{$keywordLower}%"])
                      ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"ar\"'))) LIKE ?", ["%{$keywordLower}%"]);
                });
            })
            ->filterColumn('user', function($query, $keyword) {
                $query->whereHas('user', function($q) use ($keyword) {
                    $keyword = trim($keyword);
                    if (empty($keyword)) {
                        return;
                    }
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%");
                });
            });
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<ProductNote>
     */
    public function query(ProductNote $model): QueryBuilder
    {
        return $model->with(['product', 'user'])->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('product-notes-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(4, 'desc') // Order by date desc
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
            Column::make('product')->title(__('admin.product'))->addClass('text-start'),
            Column::make('user')->title(__('admin.user'))->addClass('text-start'),
            Column::make('note')->title(__('admin.note'))->addClass('text-start'),
            Column::make('date')->title(__('admin.date'))->addClass('text-start'),
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
        return 'Product Notes ' . date('Y-m-d');
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
