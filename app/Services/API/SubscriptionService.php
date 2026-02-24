<?php

namespace App\Services\API;

use App\Http\Traits\ApiResponses;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    use ApiResponses;

    /**
     * Get all active subscription plans
     */
    public function get_plans_service($request)
    {
        try {
            // Cache plans for 5 minutes for performance
            $plans = Cache::remember('active_subscription_plans', 300, function () {
                return SubscriptionPlan::where('is_active', true)
                    ->orderBy('sort_order')
                    ->get()
                    ->map(function ($plan) {
                        return [
                            'id' => $plan->id,
                            'name' => $plan->name,
                            'amount' => (float) $plan->amount,
                            'days' => $plan->days,
                            'features' => $plan->features ?? [],
                            'is_popular' => $plan->is_popular,
                            'is_active' => $plan->is_active,
                            'currency' => 'INR',
                        ];
                    });
            });

            return $this->successResponse([
                'message' => 'Plans retrieved successfully',
                'data' => [
                    'plans' => $plans,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('SubscriptionService get_plans_service error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse([], 'Failed to retrieve plans. Please try again.', 500);
        }
    }
}

