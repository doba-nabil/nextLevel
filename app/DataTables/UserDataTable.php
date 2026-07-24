<?php

namespace App\DataTables;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<User> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('status', function ($q) {
                // Check if user is active (status is 'active')
                $checked = ($q->status === 'active') ? 'checked' : '';
                $toggleUrl = route('users.toggle-active', $q->id);
                return '<div class="form-check form-switch">
                    <input class="form-check-input status-toggle" type="checkbox"
                           data-id="' . $q->id . '"
                           data-url="' . $toggleUrl . '"
                           ' . $checked . '>
                </div>';
            })
            ->addColumn('user', function ($category) {
                $url = $category->getFirstMediaUrl('users');
                $img = $url
                    ? '<img height="30" width="30" style="object-fit:cover; border-radius:50%; margin-right:8px;" src="' . $url . '" alt="user image">'
                    : '<span class="avatar-placeholder me-2">👤</span>';

                return '<div class="d-flex align-items-center">'
                    . $img .
                    '<span>' . e($category->name) . '</span>
        </div>';
            })
            ->editColumn('wallet', fn($user) => round($user->wallet_balance ?? 0, 2))
            ->editColumn('points', function ($category) {
                return $category->points;
            })
            ->addColumn('action', function ($category) {
                $editUrl = url('/admin/users/' . $category->id . '/edit');
                $deleteUrl = url('/admin/users/' . $category->id);
                $walletUrl = url('/admin/wallets?user_id=' . $category->id);
                $convertPointsUrl = route('users.convert-points', $category->id);
                $hasPoints = ($category->points ?? 0) > 0;
                $convertPointsBtn = $hasPoints ? '
                <li>
                    <a href="javascript:void(0)" class="dropdown-item convert-points-table-btn"
                        data-user-id="' . $category->id . '"
                        data-points="' . ($category->points ?? 0) . '"
                        data-url="' . $convertPointsUrl . '">
                        <i class="icon-base ti tabler-exchange"></i> ' . __("admin.convert_to_wallet") . '
                    </a>
                </li>' : '';

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
                    <a href="' . $walletUrl . '" class="dropdown-item">
                        <i class="icon-base ti tabler-wallet"></i> ' . __("admin.wallet") . '
                    </a>
                </li>
                ' . $convertPointsBtn . '
                <li>
                    <a href="' . route('users.addresses', $category->id) . '" class="dropdown-item">
                        <i class="icon-base ti tabler-map-pin"></i> ' . __("admin.addresses") . '
                    </a>
                </li>
                <li>
                    <a href="' . route('users.orders', $category->id) . '" class="dropdown-item">
                        <i class="icon-base ti tabler-shopping-cart"></i> ' . __("admin.orders") . '
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="dropdown-item delete-btn"
                        data-id="' . $category->id . '"
                        data-url="' . $deleteUrl . '"
                        data-table=".table"
                        title="' . __("admin.delete") . '">
                       <i class="icon-base ti tabler-trash"></i> ' . __("admin.delete") . '
                    </a>
                </li>
            </ul>
        </div>';
            })
            ->rawColumns(['action', 'user', 'status'])
            ->setRowId('id')
            ->orderColumn('wallet', 'wallet_balance $1')
            ->filterColumn('user', function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->filterColumn('wallet', function ($query, $keyword) {
                $query->where('wallets.balance', 'like', "%{$keyword}%");
            })
            ->filterColumn('points', function ($query, $keyword) {
                $query->where('users.points', 'like', "%{$keyword}%");
            });

    }


    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<User>
     */
    public function query(User $model): QueryBuilder
    {
        return $model->newQuery()->where('is_admin', 0)->leftJoin('wallets', 'wallets.owner_id', '=', 'users.id')
            ->select('users.*', 'wallets.balance as wallet_balance');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('category-table')
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
//            Column::make('id')->title('#')->addClass('text-start'),
            Column::make('DT_RowIndex')
                ->title('#')
                ->addClass('text-start')
                ->orderable(false)
                ->searchable(false),
            Column::make('user')->title(__('admin.name'))->addClass('text-start'),
            Column::make('phone')->title(__('admin.phone'))->addClass('text-start'),
            Column::make('email')->title(__('admin.email'))->addClass('text-start'),
            Column::make('wallet')->title(__('admin.wallet_amount'))->addClass('text-start'),
            Column::make('points')->title(__('admin.points'))->addClass('text-start'),
            Column::make('status')->title(__('admin.status'))->addClass('text-start'),
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
        return 'Users ' . date('Y-m-d');
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
