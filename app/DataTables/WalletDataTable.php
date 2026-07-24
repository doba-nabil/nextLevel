<?php

namespace App\DataTables;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use App\Models\Wallet;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class WalletDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Transaction> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('user', fn($transaction) => optional($transaction->user)->name ?? '-')
            ->addColumn('type', function ($transaction) {
                return __('admin.'.$transaction->type);
            })
            ->addColumn('notes', function ($transaction) {
                return $transaction->meta['notes'] ?? '-';
            })
            ->setRowId('id')
            ->editColumn('amount', function ($transaction) {
                $amount = $transaction->amount;
                $formattedAmount = number_format($amount, 3);

                $class = $amount >= 0 ? 'text-success' : 'text-danger';

                return '<span class="'.$class.'">'.$formattedAmount.'</span>';
            })
            ->rawColumns(['amount'])
            ->filterColumn('user', function ($query, $keyword) {
                $query->whereHas('wallet.user', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            });
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Transaction>
     */
    public function query(Transaction $model): QueryBuilder
    {
        $query = $model->newQuery()->with('wallet.user');
        if ($userId = $this->request()->get('user_id')) {
            $query->whereHas('wallet', function ($q) use ($userId) {
                $q->where('owner_id', $userId);
            });
        }
        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('wallet-table')
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
//            Column::make('id')->title('#')->addClass('text-start'),
            Column::make('user')->title(__('admin.user'))->addClass('text-start'),
            Column::make('type')->title(__('admin.type'))->addClass('text-start'),
            Column::make('amount')->title(__('admin.amount'))->addClass('text-start'),
            Column::make('notes')->title(__('admin.notes'))->addClass('text-start'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'wallets ' . date('Y-m-d');
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
