<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\PaymentTransactionDataTable;
use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Services\Admin\PaymentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public $viewData = [];

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        $this->viewData = [
            'title' => 'Payments',
            'dataTableID' => 'payment-transaction-table',
        ];
    }

    public function index(PaymentTransactionDataTable $dataTable)
    {
        return $dataTable->render('admin.payments.index', ['viewData' => $this->viewData]);
    }

    public function show(string $id)
    {
        $transaction = $this->paymentService->getTransactionDetail($id);
        if (!$transaction) {
            abort(404, 'Transaction not found');
        }
        return view('admin.payments.show', [
            'viewData' => array_merge($this->viewData, ['title' => 'Transaction Detail']),
            'transaction' => $transaction,
        ]);
    }

    public function show_detail(Request $request)
    {
        $id = $request->input('id');
        $transaction = $this->paymentService->getTransactionDetail($id);

        if (!$transaction) {
            return response()->json(['status' => 'error', 'message' => 'Transaction not found'], 404);
        }

        $html = view('admin.payments.partials.detail', compact('transaction'))->render();

        return response()->json([
            'status' => 'success',
            'view' => $html,
        ]);
    }

    public function export(): StreamedResponse
    {
        $filename = 'payments_' . date('Y-m-d_His') . '.csv';
        $headers = ['#', 'Transaction ID', 'Android ID', 'Plan', 'Amount', 'Status', 'Gateway', 'Paid At'];
        $query = PaymentTransaction::with(['plan', 'gateway'])->orderBy('created_at', 'desc');

        return response()->streamDownload(function () use ($headers, $query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            $i = 0;
            foreach ($query->cursor() as $row) {
                $i++;
                fputcsv($handle, [
                    $i,
                    $row->transaction_id ?? '-',
                    $row->android_id ?? '-',
                    $row->plan ? $row->plan->name : '-',
                    ($row->currency ?? 'INR') . ' ' . number_format((float) $row->amount, 2),
                    $row->status,
                    $row->gateway ? $row->gateway->name : '-',
                    $row->paid_at ? $row->paid_at->format('d M Y H:i') : '-',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
