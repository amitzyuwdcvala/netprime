<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        if ($startDate && $endDate && strtotime($startDate) !== false && strtotime($endDate) !== false) {
            if (strtotime($startDate) > strtotime($endDate)) {
                $endDate = $startDate;
            }
        } else {
            $startDate = null;
            $endDate = null;
        }
        $stats = $this->dashboardService->getDashboardStats($startDate, $endDate);

        return view('admin.dashboard.index', compact('stats'));
    }
}
