<?php

namespace App\Services\Admin;

use App\Constants\PaymentStatus;
use App\Constants\SubscriptionStatus;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Cache TTL in seconds (30 minutes).
     */
    public const CACHE_TTL = 1800;

    /**
     * Cache key for dashboard stats.
     */
    public const CACHE_KEY = 'dashboard_stats';

    /**
     * Get dashboard statistics for admin home (cached).
     */
    public function getDashboardStats(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->getDashboardStatsFresh();
        });
    }

    /**
     * Get dashboard statistics without cache.
     */
    protected function getDashboardStatsFresh(): array
    {
        $totalUsers = User::count();

        $activeSubscriptions = UserSubscription::where('status', SubscriptionStatus::ACTIVE)
            ->where('end_date', '>=', now()->toDateString())
            ->count();

        $totalRevenue = (float) PaymentTransaction::where('status', PaymentStatus::SUCCESS)
            ->sum('amount');

        $totalPlans = SubscriptionPlan::count();
        $activePlans = SubscriptionPlan::where('is_active', true)->count();
        $totalGateways = PaymentGateway::count();

        $planStats = $this->getPlanStats();
        $chartData = $this->getPlanChartData();

        return [
            'total_users' => $totalUsers,
            'active_subscriptions' => $activeSubscriptions,
            'total_revenue' => $totalRevenue,
            'total_plans' => $totalPlans,
            'active_plans' => $activePlans,
            'total_gateways' => $totalGateways,
            'plan_stats' => $planStats,
            'chart_labels' => $chartData['labels'],
            'chart_subscriptions' => $chartData['subscriptions'],
            'chart_revenue' => $chartData['revenue'],
        ];
    }

    /**
     * Clear dashboard cache. Call when plans, gateways, or subscriptions change.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get per-plan stats: active subscriptions count, total purchases, revenue.
     */
    public function getPlanStats(): array
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->get();
        $activeCounts = UserSubscription::where('status', SubscriptionStatus::ACTIVE)
            ->where('end_date', '>=', now()->toDateString())
            ->select('plan_id', DB::raw('count(*) as count'))
            ->groupBy('plan_id')
            ->pluck('count', 'plan_id');

        $paymentCounts = PaymentTransaction::where('status', PaymentStatus::SUCCESS)
            ->select('plan_id', DB::raw('count(*) as count'))
            ->groupBy('plan_id')
            ->pluck('count', 'plan_id');

        $revenueByPlan = PaymentTransaction::where('status', PaymentStatus::SUCCESS)
            ->select('plan_id', DB::raw('sum(amount) as total'))
            ->groupBy('plan_id')
            ->pluck('total', 'plan_id');

        $result = [];
        foreach ($plans as $plan) {
            $result[] = [
                'name' => $plan->name,
                'active_subscriptions' => (int) ($activeCounts[$plan->id] ?? 0),
                'total_purchases' => (int) ($paymentCounts[$plan->id] ?? 0),
                'revenue' => (float) ($revenueByPlan[$plan->id] ?? 0),
            ];
        }
        return $result;
    }

    /**
     * Get data for dashboard charts (labels + subscription counts + revenue per plan).
     */
    protected function getPlanChartData(): array
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->get();
        $labels = $plans->pluck('name')->toArray();

        $activeCounts = UserSubscription::where('status', SubscriptionStatus::ACTIVE)
            ->where('end_date', '>=', now()->toDateString())
            ->select('plan_id', DB::raw('count(*) as count'))
            ->groupBy('plan_id')
            ->pluck('count', 'plan_id');

        $revenueByPlan = PaymentTransaction::where('status', PaymentStatus::SUCCESS)
            ->select('plan_id', DB::raw('sum(amount) as total'))
            ->groupBy('plan_id')
            ->pluck('total', 'plan_id');

        $subscriptions = [];
        $revenue = [];
        foreach ($plans as $plan) {
            $subscriptions[] = (int) ($activeCounts[$plan->id] ?? 0);
            $revenue[] = round((float) ($revenueByPlan[$plan->id] ?? 0), 2);
        }

        return [
            'labels' => $labels,
            'subscriptions' => $subscriptions,
            'revenue' => $revenue,
        ];
    }
}
