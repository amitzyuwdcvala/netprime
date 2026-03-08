<?php

namespace Database\Seeders;

use App\Constants\SubscriptionStatus;
use App\Models\PaymentGateway;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AddUsersSeeder extends Seeder
{
    /**
     * Preview / dummy users. Add more entries to seed multiple users.
     * - android_id: required
     * - is_vip: true/false
     * - plan_id: required if is_vip (use plan UUID from subscription_plans), or null to pick first active plan
     * - start_date: Y-m-d, or null for today
     * - end_date: Y-m-d, or null to compute from plan days from start_date
     * - paid_amount: 0 for manual/gift, or actual amount if paid via gateway
     * - payment_gateway: 'razorpay' to set Razorpay as gateway (for “paid” look), or null for manual/no gateway
     */
    protected array $users = [
        [
            'android_id'      => 'b2c3d4e5f6a17002',
            'is_vip'          => true,
            'plan_name'       => 'Monthly',
            'plan_id'         => null,
            'start_date'      => '2026-03-01',
            'end_date'        => '2026-03-31',
            'paid_amount'     => 0,
            'payment_gateway' => null,
            'scenario'        => 'VIP manual/gift, Monthly',
        ],
    ];

    public function run(): void
    {
        $razorpay = PaymentGateway::where('name', 'Razorpay')->first();
        $defaultPlan = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->first();

        foreach ($this->users as $row) {
            $plan = null;
            if (!empty($row['plan_id'])) {
                $plan = SubscriptionPlan::find($row['plan_id']);
            } elseif (!empty($row['plan_name'])) {
                $plan = SubscriptionPlan::where('name', $row['plan_name'])->where('is_active', true)->first();
            }
            if ($row['is_vip'] && !$plan) {
                $plan = $defaultPlan;
            }
            if ($row['is_vip'] && !$plan) {
                $this->command->warn("Skipping user {$row['android_id']}: is_vip but no plan found in DB.");
                continue;
            }

            $days = $plan ? (int) $plan->days : 30;
            $startDate = $row['start_date'] ? Carbon::parse($row['start_date'])->toDateString() : now()->toDateString();
            if (isset($row['end_date_offset_days'])) {
                $endDate = now()->addDays((int) $row['end_date_offset_days'])->toDateString();
            } elseif (!empty($row['end_date'])) {
                $endDate = Carbon::parse($row['end_date'])->toDateString();
            } else {
                $endDate = Carbon::parse($startDate)->addDays($days)->toDateString();
            }

            $user = User::firstOrCreate(
                ['android_id' => $row['android_id']],
                [
                    'is_vip'           => $row['is_vip'],
                    'video_click_count' => 0,
                    'added_by'         => null,
                ]
            );

            if ($row['is_vip'] && $plan) {
                $gatewayId = null;
                if (!empty($row['payment_gateway']) && strtolower($row['payment_gateway']) === 'razorpay' && $razorpay) {
                    $gatewayId = $razorpay->id;
                }

                $hasActive = UserSubscription::where('android_id', $user->android_id)
                    ->where('status', SubscriptionStatus::ACTIVE)
                    ->where('end_date', '>=', now()->toDateString())
                    ->exists();

                if (!$hasActive) {
                    UserSubscription::create([
                        'android_id'          => $user->android_id,
                        'plan_id'             => $plan->id,
                        'payment_gateway_id'  => $gatewayId,
                        'gateway_order_id'    => null,
                        'gateway_payment_id'  => null,
                        'paid_amount'         => $row['paid_amount'] ?? 0,
                        'days'                => $days,
                        'start_date'          => $startDate,
                        'end_date'            => $endDate,
                        'status'              => SubscriptionStatus::ACTIVE,
                    ]);
                }
            }

            $scenario = $row['scenario'] ?? '';
            $this->command->info("User {$row['android_id']} " . ($row['is_vip'] ? "VIP until {$endDate}" : 'non-VIP') . ($scenario ? " [{$scenario}]" : ''));
        }
    }
}
