<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $start = $this->request()->get('start', 0);

        return (new EloquentDataTable($query))
            ->addColumn('#', function () use (&$start) {
                return ++$start;
            })
            ->editColumn('is_vip', function ($query) {
                return $query->is_vip
                    ? '<span class="badge badge-success">VIP</span>'
                    : '<span class="badge badge-secondary">No</span>';
            })
            ->editColumn('created_at', function ($query) {
                return $query->created_at ? $query->created_at->format('d M Y H:i') : '-';
            })
            ->addColumn('action', function ($query) {
                $androidId = e($query->android_id);
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-primary edit-record-btn" data-id="' . $androidId . '" title="Edit"><i class="fas fa-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger delete-record-btn ml-1" data-id="' . $androidId . '" title="Delete"><i class="fas fa-trash"></i></button>';

                return $editBtn . $deleteBtn;
            })
            ->rawColumns(['#', 'is_vip', 'action']);
    }

    public function query(User $model): QueryBuilder
    {
        $query = $model->newQuery();

        if (request()->has('is_vip') && request('is_vip') != '') {
            $query->where('is_vip', request('is_vip'));
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('user-table')
            ->columns($this->getColumns())
            ->responsive(true)
            ->minifiedAjax()
            ->dom('<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>')
            ->orderBy(1, 'desc')
            ->parameters([
                'pageLength' => 25,
                'language' => [
                    'emptyTable' => 'No users found',
                ],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('#')->title('#')->orderable(false)->searchable(false)->width(50),
            Column::make('android_id')->title('Android ID'),
            Column::make('is_vip')->title('VIP')->orderable(false)->addClass('text-center'),
            Column::make('video_click_count')->title('Video Clicks')->addClass('text-center'),
            Column::make('created_at')->title('Registered'),
            Column::computed('action')->title('Action')->orderable(false)->searchable(false)->width(120)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Users_' . date('YmdHis');
    }
}
