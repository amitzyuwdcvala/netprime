<?php

namespace App\DataTables;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SettingDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('#', function ($query) {
                static $i = 0;
                return ++$i;
            })
            ->addColumn('action', function ($query) {
                $actions = '';
                $actions .= '<button class="btn btn-sm btn-primary edit-record-btn mr-1" data-id="' . $query->id . '" title="Edit"><i class="fas fa-edit"></i></button>';
                $actions .= '<button class="btn btn-sm btn-danger delete-record-btn" data-id="' . $query->id . '" title="Delete"><i class="fas fa-trash"></i></button>';
                return $actions;
            })
            ->rawColumns(['#', 'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Setting $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('id', 'desc');
    }

    /**
     * Build an HTML table.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('setting-table')
            ->columns($this->getColumns())
            ->responsive(true)
            ->minifiedAjax()
            ->dom('<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>')
            ->orderBy(0, 'asc')
            ->parameters([
                'pageLength' => 25,
                'language' => [
                    'emptyTable' => 'No settings found',
                ],
            ]);
    }

    /**
     * Get columns.
     */
    public function getColumns(): array
    {
        return [
            Column::make('#')->title('#')->orderable(false)->searchable(false)->width(50),
            Column::make('title')->title('Title'),
            Column::make('key')->title('Key'),
            Column::make('value')->title('Value'),
            Column::computed('action')->title('Action')->orderable(false)->searchable(false)->width(120)->addClass('text-center'),
        ];
    }

    /**
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'Settings_' . date('YmdHis');
    }
}
