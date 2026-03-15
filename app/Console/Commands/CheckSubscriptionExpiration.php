<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use App\Models\User;
use App\Constants\SubscriptionStatus;
use App\Services\API\VideoAccessService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckSubscriptionExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and expire subscriptions past their end time (run hourly for exact-time expiry). Does NOT reset video_click_count.';

    public function handle()
    {
        Log::info('[Cron] Subscription expiration check started');

        $this->info('Checking for expired subscriptions...');

        $expiredCount = 0;
        $updatedUsers = 0;
        $now = now();
        $today = $now->toDateString();

        try {
            DB::transaction(function () use (&$expiredCount, &$updatedUsers, $now, $today) {
                $expiredSubscriptions = UserSubscription::where('status', SubscriptionStatus::ACTIVE)
                    ->where(function ($q) use ($now, $today) {
                        $q->whereNotNull('end_at')->where('end_at', '<', $now)
                            ->orWhere(function ($q2) use ($today) {
                                $q2->whereNull('end_at')->where('end_date', '<', $today);
                            });
                    })
                    ->get();

                foreach ($expiredSubscriptions as $subscription) {
                    // Update subscription status
                    $subscription->status = SubscriptionStatus::EXPIRED;
                    $subscription->save();

                    // Set is_vip = false. Do NOT reset video_click_count – user keeps remaining free videos (e.g. had 4 left → still has 4 after plan end).
                    $user = User::where('android_id', $subscription->android_id)->first();
                    if ($user && $user->is_vip) {
                        $user->is_vip = false;
                        $user->save();
                        $updatedUsers++;

                        // Invalidate VIP access cache so next video/access call sees is_vip = false
                        VideoAccessService::invalidateVipAccessCache($user->android_id);
                    }

                    $expiredCount++;
                }
            });

            $this->info("Successfully expired {$expiredCount} subscriptions.");
            $this->info("Updated VIP status for {$updatedUsers} users.");

            Log::info('[Cron] Subscription expiration check completed', [
                'expired_count' => $expiredCount,
                'updated_users' => $updatedUsers,
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error checking subscription expiration: ' . $e->getMessage());
            
            Log::error('[Cron] Subscription expiration check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}

