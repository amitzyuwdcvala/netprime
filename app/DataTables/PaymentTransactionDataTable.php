<?php

namespace App\DataTables;

use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PaymentTransactionDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $start = $this->request()->get('start', 0);
        return (new EloquentDataTable($query))
            ->addColumn('#', function () use (&$start) {
                return ++$start;
            })
            ->addColumn('plan_name', function ($q) {
                return $q->plan ? $q->plan->name : '-';
            })
            ->addColumn('gateway_name', function ($q) {
                return $q->gateway ? $q->gateway->name : '-';
            })
            ->editColumn('amount', function ($q) {
                return ($q->currency ?? 'INR') . ' ' . number_format((float)$q->amount, 2);
            })
            ->editColumn('status', function ($q) {
                $class = $q->status === \App\Constants\PaymentStatus::SUCCESS ? 'success' : ($q->status === \App\Constants\PaymentStatus::FAILED ? 'danger' : 'secondary');
                return '<span class="badge badge-' . $class . '">' . e($q->status) . '</span>';
            })
            ->editColumn('paid_at', function ($q) {
                return $q->paid_at ? $q->paid_at->format('d M Y H:i') : '-';
            })
            ->addColumn('action', function ($q) {
                return '<a href="' . e(route('admin.payments.show', $q->id)) . '" class="btn btn-sm btn-info" title="View detail"><i class="fas fa-eye"></i></a>';
            })
            ->rawColumns(['#', 'status', 'action']);
    }

    public function query(PaymentTransaction $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['plan', 'gateway']);

        if (request()->has('filter_status') && request('filter_status') != '') {
            $query->where('status', request('filter_status'));
        }

        $startDate = request('filter_start_date');
        $endDate = request('filter_end_date');
        if ($startDate && strtotime($startDate) !== false) {
            $query->where(\Illuminate\Support\Facades\DB::raw('COALESCE(paid_at, created_at)'), '>=', $startDate . ' 00:00:00');
        }
        if ($endDate && strtotime($endDate) !== false) {
            $query->where(\Illuminate\Support\Facades\DB::raw('COALESCE(paid_at, created_at)'), '<=', $endDate . ' 23:59:59');
        }

        $query->orderBy('created_at', 'desc');
        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()->setTableId('payment-transaction-table')->columns($this->getColumns())->responsive(true)->minifiedAjax()
            ->dom('<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>')->orderBy(1, 'desc')
            ->parameters(['pageLength' => 25, 'language' => ['emptyTable' => 'No transactions found']]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('#')->title('#')->orderable(false)->searchable(false)->width(50),
            Column::make('transaction_id')->title('Transaction ID'),
            Column::make('android_id')->title('Android ID'),
            Column::make('plan_name')->title('Plan')->orderable(false)->searchable(false),
            Column::make('amount')->title('Amount')->addClass('text-center'),
            Column::make('status')->title('Status')->addClass('text-center'),
            Column::make('gateway_name')->title('Gateway')->orderable(false)->searchable(false),
            Column::make('paid_at')->title('Paid At'),
            Column::computed('action')->title('Action')->orderable(false)->searchable(false)->width(80)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'PaymentTransaction_' . date('YmdHis');
    }
}
