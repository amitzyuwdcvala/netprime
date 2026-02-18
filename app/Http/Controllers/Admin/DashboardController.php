<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponses;

class DashboardController extends Controller
{
    use ApiResponses;

    /**
     * Display admin dashboard.
     */
    public function index()
    {
        return view('admin.dashboard.index');
    }
}
