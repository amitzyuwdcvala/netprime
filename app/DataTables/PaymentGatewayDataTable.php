<?php

namespace App\DataTables;

use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PaymentGatewayDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $start = $this->request()->get('start', 0);

        return (new EloquentDataTable($query))
            ->addColumn('#', function ($row) use (&$start) {
                return ++$start;
            })
            ->addColumn('is_active', function ($query) {
                return view('components.active-toggle', ['model' => $query, 'asSwitch' => true]);
            })
            ->addColumn('action', function ($query) {
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-primary edit-record-btn" data-id="' . e($query->id) . '" title="Edit"><i class="fas fa-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger delete-record-btn ml-1" data-id="' . e($query->id) . '" title="Delete"><i class="fas fa-trash"></i></button>';

                return $editBtn . $deleteBtn;
            })
            ->rawColumns(['#', 'is_active', 'action']);
    }

    public function query(PaymentGateway $model): QueryBuilder
    {
        $query = $model->newQuery();

        if (request()->has('filter_active') && request('filter_active') != '') {
            $query->where('is_active', request('filter_active'));
        }

        $query->orderBy('sort_order', 'asc');
        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('payment-gateway-table')
            ->columns($this->getColumns())
            ->responsive(true)
            ->minifiedAjax()
            ->dom('<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>')
            ->orderBy(1)
            ->parameters([
                'pageLength' => 25,
                'language' => [
                    'emptyTable' => 'No gateways found',
                ],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('#')->title('#')->orderable(false)->searchable(false)->width(50),
            Column::make('name')->title('Name'),
            Column::make('code')->title('Code'),
            Column::make('display_name')->title('Display Name'),
            Column::make('is_active')->title('Active')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('sort_order')->title('Order')->addClass('text-center'),
            Column::computed('action')->title('Action')->orderable(false)->searchable(false)->width(120)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'PaymentGateway_' . date('YmdHis');
    }
}
