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

    /**
     * Display admin dashboard.
     */
    public function index()
    {
        $stats = $this->dashboardService->getDashboardStats();

        return view('admin.dashboard.index', compact('stats'));
    }
}
