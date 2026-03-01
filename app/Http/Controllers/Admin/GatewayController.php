<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\PaymentGatewayDataTable;
use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Services\Admin\GatewayService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GatewayController extends Controller
{
    protected GatewayService $gatewayService;

    public $viewData = [];

    public function __construct(GatewayService $gatewayService)
    {
        $this->gatewayService = $gatewayService;
        $this->viewData = [
            'title' => 'Payment Gateways',
            'permission' => 'gateway',
            'prefix' => 'gateway_',
            'dataTableID' => 'payment-gateway-table',
            'canvasId' => 'manage-record',
            'canvasSize' => 'canvas-sm',
            'canvasHeading' => 'Manage Payment Gateway',
            'deleteRoute' => route('admin.gateways.delete'),
            'manageRoute' => route('admin.gateways.manage'),
        ];
    }

    public function index(PaymentGatewayDataTable $dataTable)
    {
        return $dataTable->render('admin.gateways.index', ['viewData' => $this->viewData]);
    }

    public function manage_gateway(Request $request)
    {
        return $this->gatewayService->manage_gateway_service($request);
    }

    public function save_gateway(Request $request)
    {
        return $this->gatewayService->save_gateway_service($request);
    }

    public function delete_gateway(Request $request)
    {
        return $this->gatewayService->delete_gateway_service($request);
    }

    public function export(): StreamedResponse
    {
        $filename = 'gateways_' . date('Y-m-d_His') . '.csv';
        $headers = ['#', 'Name', 'Code', 'Display Name', 'Active', 'Order'];
        $query = PaymentGateway::query()->orderBy('sort_order');

        return response()->streamDownload(function () use ($headers, $query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            $i = 0;
            foreach ($query->cursor() as $row) {
                $i++;
                fputcsv($handle, [
                    $i,
                    $row->name,
                    $row->code,
                    $row->display_name ?? '-',
                    $row->is_active ? 'Yes' : 'No',
                    $row->sort_order,
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
