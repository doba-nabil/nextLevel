<?php

namespace App\DataTables;

use App\Models\Location;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class LocationDataTable extends DataTable
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

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
            ->addColumn('parent_name', fn($location) => $location->parent ? $location->parent->name : '-')
            ->addColumn('currency', fn($location) => $location->currency ? $location->currency->name . ' ('. $location->currency->sign .')': '-')
            ->addColumn('child_count', fn($location) => $location->children ? count($location->children) : '0')
            ->addColumn('grandparent_name', fn($location) => $this->type == 'city' && $location->parent && $location->parent->parent ? $location->parent->parent->name : '-')
            ->addColumn('phone_code', fn($location) => $this->type == 'country' ? ($location->phone_code ?? '-') : '-')
            ->addColumn('code', fn($location) => $this->type == 'country' ? ($location->code ?? '-') : '-')
            ->addColumn('delivery_time', fn($location) => $this->type == 'city' ? ($location->delivery_time ? number_format($location->delivery_time, 2) . ' ' . __('admin.hours') : '-') : '-')
            ->addColumn('action', function ($location) {
                $editUrl = url(url()->current().'/' . $location->id.'/edit');
                $deleteUrl = url(url()->current().'/' . $location->id);
                $unifyBtn = '';
                
                // Add unify cities button only for states
                if ($this->type == 'state') {
                    $unifyBtn = '
                    <li>
                        <a href="javascript:void(0)" class="dropdown-item unify-cities-btn"
                            data-state-id="' . $location->id . '"
                            data-bs-toggle="modal"
                            data-bs-target="#unifyCitiesModal">
                            <i class="icon-base ti tabler-refresh"></i> ' . __("admin.unify_cities") . '
                        </a>
                    </li>';
                }
                
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
            ' . $unifyBtn . '
            <li>
            <a href="javascript:void(0)" class="dropdown-item delete-btn"
                        data-id="' . $location->id . '"
                        data-url="' . $deleteUrl . '"
                        data-table=".table"
                        title="{{ __(\'admin.delete\') }}">
                   <i class="icon-base ti tabler-trash"></i> ' . __("admin.delete") . '
                </a>
            </li>
        </ul>
    </div>';
            })
            ->editColumn('name', function ($location) {
                return $location->name;
            })
            ->rawColumns(['action', 'status'])
            ->setRowId('id')
            ->filterColumn('name', function($query, $keyword) {
                $keyword = trim($keyword);
                if (empty($keyword)) {
                    return;
                }
                // Convert to lowercase for case-insensitive search
                $keywordLower = mb_strtolower($keyword, 'UTF-8');
                // Search in both English and Arabic, case-insensitive
                $query->where(function($q) use ($keywordLower) {
                    $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"en\"'))) LIKE ?", ["%{$keywordLower}%"])
                      ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"ar\"'))) LIKE ?", ["%{$keywordLower}%"]);
                });
            })
            ->filterColumn('parent_name', function($query, $keyword) {
                $keyword = trim($keyword);
                if (empty($keyword)) {
                    return;
                }
                $keywordLower = mb_strtolower($keyword, 'UTF-8');
                // Search in parent name (both languages)
                $query->whereHas('parent', function($q) use ($keywordLower) {
                    $q->where(function($subQ) use ($keywordLower) {
                        $subQ->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"en\"'))) LIKE ?", ["%{$keywordLower}%"])
                             ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"ar\"'))) LIKE ?", ["%{$keywordLower}%"]);
                    });
                });
            })
            ->filterColumn('grandparent_name', function($query, $keyword) {
                $keyword = trim($keyword);
                if (empty($keyword)) {
                    return;
                }
                $keywordLower = mb_strtolower($keyword, 'UTF-8');
                // Search in grandparent name (both languages)
                $query->whereHas('parent.parent', function($q) use ($keywordLower) {
                    $q->where(function($subQ) use ($keywordLower) {
                        $subQ->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"en\"'))) LIKE ?", ["%{$keywordLower}%"])
                             ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"ar\"'))) LIKE ?", ["%{$keywordLower}%"]);
                    });
                });
            });
    }

    public function query(Location $model): QueryBuilder
    {
        return $model->newQuery()->where('type', $this->type)->with('parent');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('location-table')
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
            ]);
    }

    public function getColumns(): array
    {
        $columns = [
            Column::make('DT_RowIndex')
                ->title('#')
                ->addClass('text-start')
                ->orderable(false)
                ->searchable(false),
            Column::make('name')->title(__('admin.name'))->addClass('text-start'),
            Column::make('code')->title(__('admin.code'))->visible($this->type == 'country')->addClass('text-start'),
            Column::make('phone_code')->title(__('admin.phone_code'))->visible($this->type == 'country')->addClass('text-start'),
            Column::make('child_count')->title(__('admin.state_count'))->visible($this->type == 'country')->addClass('text-start'),
            Column::make('currency')->title(__('admin.currency'))->visible($this->type == 'country')->addClass('text-start'),
            Column::make('child_count')->title(__('admin.city_count'))->visible($this->type == 'state')->addClass('text-start'),
            Column::make('parent_name')->title(__('admin.state'))->visible($this->type == 'city')->addClass('text-start'),
            Column::make('parent_name')->title(__('admin.country'))->visible($this->type == 'state')->addClass('text-start'),
            Column::make('grandparent_name')->title(__('admin.country'))->visible($this->type == 'city')->addClass('text-start'),
            Column::make('delivery_time')->title(__('admin.delivery_time'))->visible($this->type == 'city')->addClass('text-start'),
            Column::computed('status')->title(__('admin.status'))->addClass('text-start'),
            Column::computed('action')->title(__('admin.action'))
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-start'),
        ];
        return array_filter($columns, fn($column) => $column['visible'] ?? true);
    }

    protected function filename(): string
    {
        return ucfirst($this->type) . '_' . date('Y-m-d');
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
