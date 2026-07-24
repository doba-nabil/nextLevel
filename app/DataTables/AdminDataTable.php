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

class AdminDataTable extends DataTable
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
                switch ($q->status) {
                    case 'pending':
                        return '<span class="badge bg-label-success">' . __('admin.active') . '</span>';
                    case 'active':
                        return '<span class="badge bg-label-success">' . __('admin.active') . '</span>';
                    case 'deactive':
                        return '<span class="badge bg-label-secondary">' . __('admin.deactive') . '</span>';
                    case 'blocked':
                        return '<span class="badge bg-label-danger">' . __('admin.blocked') . '</span>';
                    default:
                        return '<span class="badge bg-label-info">' . $q->status . '</span>';
                }
            })
            ->addColumn('name', function ($admin) {
                $url = $admin->getFirstMediaUrl('users');
                $image = $url
                    ? '<img src="' . $url . '" alt="user image" style="height:30px;width:30px;object-fit:contain;margin-right:5px;border-radius:4px;">'
                    : '';

                return $image . '<span>' . $admin->name . '</span>';
            })
            ->addColumn('roles', function ($admin) {
                return $admin->roles->map(function ($role) {
                    $name = $role->display_name['ar'] ?? $role->name;
                    return '<span class="badge bg-success mt-1">' . $name . '</span>';
                })->implode('<br>');
            })
            ->addColumn('action', function ($admin) {
                $editUrl = url('/admin-panel/admins/' . $admin->id . '/edit');
                $deleteUrl = url('/admin-panel/admins/' . $admin->id);

                $editButton = '
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
            </ul>
        </div>';

                if ($admin->id == 1) {
                    return $editButton;
                }

                $deleteButton = '
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
                        data-id="' . $admin->id . '"
                        data-url="' . $deleteUrl . '"
                        data-table=".table"
                        title="' . __("admin.delete") . '">
                        <i class="icon-base ti tabler-trash"></i> ' . __("admin.delete") . '
                    </a>
                </li>
            </ul>
        </div>';

                return $deleteButton;
            })
            ->rawColumns(['name', 'roles', 'action', 'status'])
            ->setRowId('id')
            ->filterColumn('name', function ($query, $keyword) {
                $keyword = trim($keyword);
                if (empty($keyword)) {
                    return;
                }
                $keywordLower = mb_strtolower($keyword, 'UTF-8');
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$keywordLower}%"]);
            });
    }


    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<User>
     */
    public function query(User $model): QueryBuilder
    {
        return $model->newQuery()->where('is_admin', 1)->with('roles');
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
            Column::make('DT_RowIndex')
                ->title('#')
                ->addClass('text-start')
                ->orderable(false)
                ->searchable(false),
            Column::make('name')->title(__('admin.name'))->addClass('text-start'),
            Column::make('phone')->title(__('admin.phone'))->addClass('text-start'),
            Column::make('email')->title(__('admin.email'))->addClass('text-start'),
            Column::make('roles')->title(__('admin.roles'))->addClass('text-start'),
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
        return 'Supervisors ' . date('Y-m-d');
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
