<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use App\Models\User;
use App\Constants\SubscriptionStatus;
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
    protected $description = 'Check and expire subscriptions that have passed their end date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
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

                    // Update user VIP status
                    $user = User::find($subscription->android_id);
                    if ($user && $user->is_vip) {
                        $user->is_vip = false;
                        $user->save();
                        $updatedUsers++;
                    }

                    $expiredCount++;
                }
            });

            $this->info("Successfully expired {$expiredCount} subscriptions.");
            $this->info("Updated VIP status for {$updatedUsers} users.");

            Log::info("Subscription expiration check completed", [
                'expired_count' => $expiredCount,
                'updated_users' => $updatedUsers,
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error checking subscription expiration: ' . $e->getMessage());
            
            Log::error('Subscription expiration check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}

