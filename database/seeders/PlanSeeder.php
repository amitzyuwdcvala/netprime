<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $plans = [
            [
                'name' => 'Monthly',
                'amount' => 1,
                'days' => 30,
                'features' => ['30 days validatiy', '720p quality'],
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => '3 Months',
                'amount' => 249,
                'days' => 90,
                'features' => ['3 month validatiy', '1080p quality'],
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => '6 Months',
                'amount' => 399,
                'days' => 120,
                'features' => ['6 month validatiy', '1080p quality'],
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Yearly',
                'amount' => 599,
                'days' => 365,
                'features' => ['1 year validatiy', '1080p quality'],
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }
    }
}
