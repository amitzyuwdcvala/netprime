<?php

namespace App\Services\API;

use App\Http\Traits\ApiResponses;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VideoAccessService
{
    use ApiResponses;

    private const VIP_ACCESS_CACHE_TTL = 300; // 5 minutes

    private const VIP_ACCESS_CACHE_PREFIX = 'video_access_vip:';

    /**
     * User is set by AndroidAuth from android_id (header X-Android-Id or body). No Sanctum/token.
     */
    public function access_video_service($request)
    {
        $user = $request->user(); // Set by AndroidAuth middleware

        if (!$user) {
            return $this->unauthorizedResponse([], 'User not authenticated');
        }

        $androidId = $user->android_id;

        $cachedVip = Cache::get(self::VIP_ACCESS_CACHE_PREFIX . $androidId);
        if ($cachedVip !== null) {
            Log::info('[VideoAccess] Access granted (VIP cache hit)', [
                'android_id' => $androidId,
                'subscription_expires_at' => $cachedVip,
            ]);
            return $this->successResponse([
                'message' => 'Video access granted',
                'data' => [
                    'access_granted' => true,
                    'remaining_count' => null,
                    'is_vip' => true,
                    'subscription_expires_at' => $cachedVip,
                ],
            ]);
        }

        try {
            // Check if subscription is expired (in case cron hasn't run)
            if ($user->isSubscriptionExpired()) {
                DB::transaction(function () use ($user, $androidId) {
                    $user->is_vip = false;
                    $user->save();

                    $subscription = $user->subscriptions;
                    if ($subscription && $subscription->status !== \App\Constants\SubscriptionStatus::EXPIRED) {
                        $subscription->status = \App\Constants\SubscriptionStatus::EXPIRED;
                        $subscription->save();
                    }
                });
                self::invalidateVipAccessCache($androidId);
            }

            // VIP with active subscription: grant access and cache so next N minutes don't hit DB
            if ($user->is_vip && $user->hasActiveSubscription()) {
                $expiresAt = $user->subscription?->end_date?->format('Y-m-d');
                Cache::put(self::VIP_ACCESS_CACHE_PREFIX . $androidId, $expiresAt, self::VIP_ACCESS_CACHE_TTL);
                Log::info('[VideoAccess] Access granted (VIP, active subscription)', [
                    'android_id' => $androidId,
                    'subscription_expires_at' => $expiresAt,
                ]);
                return $this->successResponse([
                    'message' => 'Video access granted',
                    'data' => [
                        'access_granted' => true,
                        'remaining_count' => null,
                        'is_vip' => true,
                        'subscription_expires_at' => $expiresAt,
                    ],
                ]);
            }

            // Non-VIP: need to decrement video_click_count (must use transaction)
            DB::beginTransaction();

            $user->refresh();

            if ($user->video_click_count > 0) {
                $user->video_click_count--;
                $user->save();

                DB::commit();
                Log::info('[VideoAccess] Access granted (free quota)', [
                    'android_id' => $androidId,
                    'remaining_count' => $user->video_click_count,
                ]);
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

            DB::commit();
            Log::info('[VideoAccess] Access denied (no remaining quota)', [
                'android_id' => $androidId,
                'remaining_count' => 0,
            ]);
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

    /**
     * Invalidate VIP access cache (e.g. when subscription ends or is cancelled).
     * Call this from CheckSubscriptionExpiration or when subscription status changes.
     */
    public static function invalidateVipAccessCache(string $androidId): void
    {
        Cache::forget(self::VIP_ACCESS_CACHE_PREFIX . $androidId);
    }
}
