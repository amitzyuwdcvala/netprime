<?php

namespace App\Services\API;

use App\Http\Traits\ApiResponses;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    use ApiResponses;

    /** Cache key for active plans list (invalidate when plans are updated in admin). */
    public const CACHE_KEY_PLANS = 'active_subscription_plans';

    /** Cache TTL for plans (5 minutes). */
    public const CACHE_TTL_PLANS = 300;

    /**
     * Get all active subscription plans
     */
    public function get_plans_service($request)
    {
        try {
            $androidId = $request->header('X-Android-ID', 'unknown');
            Log::info('[Plans] Request received', ['android_id' => $androidId]);

            // Cache plans for 5 minutes for performance
            $plans = Cache::remember(self::CACHE_KEY_PLANS, self::CACHE_TTL_PLANS, function () {
                $fetched = SubscriptionPlan::where('is_active', true)
                    ->orderBy('sort_order')
                    ->get()
                    ->map(function ($plan) {
                        return [
                            'id'         => $plan->id,
                            'name'       => $plan->name,
                            'amount'     => (float) $plan->amount,
                            'days'       => $plan->days,
                            'features'   => $plan->features ?? [],
                            'is_popular' => $plan->is_popular,
                            'is_active'  => $plan->is_active,
                            'currency'   => 'INR',
                        ];
                    });
                Log::info('[Plans] Fetched from DB', ['count' => $fetched->count()]);
                return $fetched;
            });

            Log::info('[Plans] Returning plans (from cache or DB)', [
                'count'    => count($plans),
                'plan_ids' => collect($plans)->pluck('id')->toArray(),
            ]);

            return $this->successResponse([
                'message' => 'Plans retrieved successfully',
                'data'    => [
                    'plans' => $plans,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[Plans] ERROR: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse([], 'Failed to retrieve plans. Please try again.', 500);
        }
    }
}
