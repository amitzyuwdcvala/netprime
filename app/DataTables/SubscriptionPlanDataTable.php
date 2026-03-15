<?php

namespace App\DataTables;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SubscriptionPlanDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $start = $this->request()->get('start', 0);
        return (new EloquentDataTable($query))
            ->addColumn('#', function () use (&$start) {
                return ++$start;
            })
            ->editColumn('amount', function ($q) {
                return 'Rs.' . number_format((float)$q->amount, 2);
            })
            ->editColumn('is_popular', function ($q) {
                return $q->is_popular ? '<span class="badge badge-info">Popular</span>' : '<span class="badge badge-secondary">-</span>';
            })
            ->editColumn('is_active', function ($q) {
                return $q->is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($q) {
                return '<button type="button" class="btn btn-sm btn-icon btn-primary edit-record-btn" data-id="' . e($q->id) . '" title="Edit"><i class="fas fa-edit"></i></button><button type="button" class="btn btn-sm btn-icon btn-danger delete-record-btn ml-1" data-id="' . e($q->id) . '" title="Delete"><i class="fas fa-trash"></i></button>';
            })
            ->rawColumns(['#', 'is_popular', 'is_active', 'action']);
    }

    public function query(SubscriptionPlan $model): QueryBuilder
    {
        $query = $model->newQuery();

        if (request()->has('filter_active') && request('filter_active') != '') {
            $query->where('is_active', request('filter_active'));
        }
        if (request()->has('filter_popular') && request('filter_popular') != '') {
            $query->where('is_popular', request('filter_popular'));
        }

        $query->orderBy('sort_order', 'asc');
        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()->setTableId('subscription-plan-table')->columns($this->getColumns())->responsive(true)->minifiedAjax()
            ->dom('<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>')->orderBy(1)
            ->parameters(['pageLength' => 25, 'language' => ['emptyTable' => 'No plans found']]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('#')->title('#')->orderable(false)->searchable(false)->width(50),
            Column::make('name')->title('Name'),
            Column::make('amount')->title('Amount')->addClass('text-center'),
            Column::make('days')->title('Days')->addClass('text-center'),
            Column::make('is_popular')->title('Popular')->orderable(false)->addClass('text-center'),
            Column::make('is_active')->title('Status')->orderable(false)->addClass('text-center'),
            Column::make('sort_order')->title('Order')->addClass('text-center'),
            Column::computed('action')->title('Action')->orderable(false)->searchable(false)->width(120)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'SubscriptionPlan_' . date('YmdHis');
    }
}
