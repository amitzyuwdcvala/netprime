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
    protected $description = 'Check and expire subscriptions that have passed their end date (daily cron). Does NOT reset video_click_count – remaining free videos persist after plan end.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('[Cron] Subscription expiration check started');

        $this->info('Checking for expired subscriptions...');

        $expiredCount = 0;
        $updatedUsers = 0;

        try {
            DB::transaction(function () use (&$expiredCount, &$updatedUsers) {
                // Find all active subscriptions that have expired
                $expiredSubscriptions = UserSubscription::where('status', SubscriptionStatus::ACTIVE)
                    ->where('end_date', '<', now()->toDateString())
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

