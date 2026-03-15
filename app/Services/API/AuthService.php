<?php

namespace App\Services\API;

use App\Http\Traits\ApiResponses;
use App\Models\AppConfig;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AuthService
{
    use ApiResponses;

    /**
     * Register user (first time app launch)
     */
    public function register_service($request)
    {
        try {
            DB::beginTransaction();
            // dd($request->all());
            $androidId = $request->header('X-Android-Id') ?? $request->header('X-Android-ID') ?? $request->input('android_id');

            $user = User::find($androidId);

            if ($user) {
                DB::commit();

                return $this->successResponse([
                    'message' => 'User already registered',
                    'data' => [
                        'user'   => $this->buildUserPayload($user),
                        'config' => AppConfig::getDecodedCached(),
                    ],
                ]);
            }

            // Create new user
            $user = User::create([
                'android_id' => $androidId,
                'is_vip' => false,
                'video_click_count' => 5,
            ]);


            DB::commit();

            return $this->successResponse([
                'message' => 'User registered successfully',
                'data' => [
                    'user'   => $this->buildUserPayload($user),
                    'config' => AppConfig::getDecodedCached(),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AuthService register_service error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse([], 'Registration failed. Please try again.', 500);
        }
    }

    /**
     * Build user payload including active subscription details (if any).
     */
    protected function buildUserPayload(User $user): array
    {
        $payload = [
            'android_id'        => $user->android_id,
            'is_vip'            => $user->is_vip,
            'video_click_count' => $user->video_click_count,
            'subscription'      => null,
        ];

        // Only include subscription details if there is an active subscription
        $subscription = $user->activeSubscription()->with('plan')->first();

        if ($subscription && $subscription->plan) {
            $now = Carbon::now()->startOfDay();
            $end = $subscription->end_date
                ? Carbon::parse($subscription->end_date)->startOfDay()
                : null;

            $remainingDays = null;
            if ($end && $end->gte($now)) {
                $remainingDays = (int) $now->diffInDays($end);
            }

            $plan = $subscription->plan;

            $payload['subscription'] = [
                'id'             => $subscription->id,
                'status'         => $subscription->status,
                'start_date'     => $subscription->start_date?->format('Y-m-d'),
                'end_date'       => $subscription->end_date?->format('Y-m-d'),
                'remaining_days' => $remainingDays,
                'plan' => [
                    'id'       => $plan->id,
                    'name'     => $plan->name,
                    'amount'   => (float) $plan->amount,
                    'days'     => (int) $plan->days,
                    'features' => $plan->features ?? [],
                    'currency' => 'INR',
                ],
            ];
        }

        return $payload;
    }
}
