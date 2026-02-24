<?php

namespace App\Services\API;

use App\Http\Traits\ApiResponses;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VideoAccessService
{
    use ApiResponses;

    /**
     * Handle video access request
     */
    public function access_video_service($request)
    {
        try {
            DB::beginTransaction();

            $user = $request->user(); // Get user from auth

            if (!$user) {
                return $this->unauthorizedResponse([], 'User not authenticated');
            }

            // Check if subscription is expired (in case cron hasn't run)
            if ($user->isSubscriptionExpired()) {
                $user->is_vip = false;
                $user->save();

                // Update subscription status
                $subscription = $user->subscriptions;
                if ($subscription && $subscription->status !== \App\Constants\SubscriptionStatus::EXPIRED) {
                    $subscription->status = \App\Constants\SubscriptionStatus::EXPIRED;
                    $subscription->save();
                }
            }

            // Check if user is VIP with active subscription
            if ($user->is_vip && $user->hasActiveSubscription()) {
                DB::commit();

                return $this->successResponse([
                    'message' => 'Video access granted',
                    'data' => [
                        'access_granted' => true,
                        'remaining_count' => null,
                        'is_vip' => true,
                        'subscription_expires_at' => $user->subscriptions?->end_date?->format('Y-m-d'),
                    ],
                ]);
            }

            // Check if user has video clicks remaining
            if ($user->video_click_count > 0) {
                $user->video_click_count--;
                $user->save();

                DB::commit();

                return $this->successResponse([
                    'message' => 'Video access granted',
                    'data' => [
                        'access_granted' => true,
                        'remaining_count' => $user->video_click_count,
                        'is_vip' => false,
                        'subscription_expires_at' => null,
                    ],
                ]);
            }

            // No access remaining
            DB::commit();

            return $this->forbiddenResponse([
                'access_granted' => false,
                'remaining_count' => 0,
                'is_vip' => false,
            ], 'You have no video access remaining. Please subscribe to a plan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('VideoAccessService access_video_service error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse([], 'Failed to process video access. Please try again.', 500);
        }
    }
}
