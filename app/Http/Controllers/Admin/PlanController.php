<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\SubscriptionPlanDataTable;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\Admin\PlanService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlanController extends Controller
{
    protected PlanService $planService;

    public $viewData = [];

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
        $this->viewData = [
            'title' => 'Subscription Plans',
            'dataTableID' => 'subscription-plan-table',
            'canvasId' => 'manage-record',
            'canvasHeading' => 'Manage Plan',
            'deleteRoute' => route('admin.plans.delete'),
            'manageRoute' => route('admin.plans.manage'),
        ];
    }

    public function index(SubscriptionPlanDataTable $dataTable)
    {
        return $dataTable->render('admin.plans.index', ['viewData' => $this->viewData]);
    }

    public function manage_plan(Request $request)
    {
        return $this->planService->manage_plan_service($request);
    }

    public function save_plan(Request $request)
    {
        return $this->planService->save_plan_service($request);
    }

    public function delete_plan(Request $request)
    {
        return $this->planService->delete_plan_service($request);
    }

    public function export(): StreamedResponse
    {
        $filename = 'plans_' . date('Y-m-d_His') . '.csv';
        $headers = ['#', 'Name', 'Amount', 'Days', 'Popular', 'Status', 'Order'];
        $query = SubscriptionPlan::query()->orderBy('sort_order');

        return response()->streamDownload(function () use ($headers, $query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            $i = 0;
            foreach ($query->cursor() as $row) {
                $i++;
                fputcsv($handle, [
                    $i,
                    $row->name,
                    number_format((float) $row->amount, 2),
                    $row->days,
                    $row->is_popular ? 'Yes' : 'No',
                    $row->is_active ? 'Active' : 'Inactive',
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
