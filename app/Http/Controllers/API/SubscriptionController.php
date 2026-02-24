<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponses;
use App\Services\API\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    use ApiResponses;

    public $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Get all active subscription plans
     */
    public function get_plans(Request $request)
    {
        return $this->subscriptionService->get_plans_service($request);
    }
}

