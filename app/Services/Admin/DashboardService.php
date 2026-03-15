<?php

namespace App\Services\Admin;

use App\Constants\PaymentStatus;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public const CACHE_TTL = 1800;
    public const CACHE_KEY = 'dashboard_stats';

    /**
     * @param string|null $startDate Y-m-d
     * @param string|null $endDate   Y-m-d
     */
    public function getDashboardStats(?string $startDate = null, ?string $endDate = null): array
    {
        $hasDateFilter = $startDate && $endDate;
        if ($hasDateFilter) {
            return $this->getDashboardStatsFresh($startDate, $endDate);
        }
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->getDashboardStatsFresh(null, null);
        });
    }

    /**
     * @param string|null $startDate Y-m-d
     * @param string|null $endDate   Y-m-d
     */
    protected function getDashboardStatsFresh(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        if ($start && $end) {
            $totalUsers = User::whereBetween('created_at', [$start, $end])->count();
            $activeSubscriptions = UserSubscription::where('status', 'active')
                ->where('start_date', '<=', $end->toDateString())
                ->where(function ($q) use ($start) {
                    $q->where('end_date', '>=', $start->toDateString())
                        ->orWhere('end_at', '>=', $start);
                })
                ->count();
            $totalRevenue = (float) PaymentTransaction::where('status', PaymentStatus::SUCCESS)
                ->where('amount', '>', 0)
                ->whereBetween(DB::raw('COALESCE(paid_at, created_at)'), [$start, $end])
                ->sum('amount');
        } else {
            $totalUsers = User::count();
            $activeSubscriptions = UserSubscription::active()->count();
            $totalRevenue = (float) PaymentTransaction::where('status', PaymentStatus::SUCCESS)
                ->where('amount', '>', 0)
                ->sum('amount');
        }

        $totalPlans = SubscriptionPlan::count();
        $activePlans = SubscriptionPlan::where('is_active', true)->count();
        $totalGateways = PaymentGateway::count();

        $planStats = $this->getPlanStats($start, $end);
        $chartData = $this->getPlanChartData($start, $end);

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
            'filter_start' => $startDate,
            'filter_end' => $endDate,
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
     * @param Carbon|null $start
     * @param Carbon|null $end
     */
    public function getPlanStats(?\Carbon\Carbon $start = null, ?\Carbon\Carbon $end = null): array
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->get();
        $activeQ = UserSubscription::where('status', 'active');
        if ($start && $end) {
            $activeQ->where('start_date', '<=', $end->toDateString())
                ->where(function ($q) use ($start) {
                    $q->where('end_date', '>=', $start->toDateString())
                        ->orWhere('end_at', '>=', $start);
                });
        } else {
            $activeQ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNull('end_at')->where('end_date', '>=', now()->toDateString());
                })->orWhere(function ($q2) {
                    $q2->whereNotNull('end_at')->where('end_at', '>=', now());
                });
            });
        }
        $activeCounts = (clone $activeQ)->select('plan_id', DB::raw('count(*) as count'))
            ->groupBy('plan_id')
            ->pluck('count', 'plan_id');

        $paymentQ = PaymentTransaction::where('status', PaymentStatus::SUCCESS);
        if ($start && $end) {
            $paymentQ->whereBetween(DB::raw('COALESCE(paid_at, created_at)'), [$start, $end]);
        }
        $paymentCounts = (clone $paymentQ)->select('plan_id', DB::raw('count(*) as count'))
            ->groupBy('plan_id')
            ->pluck('count', 'plan_id');
        $revenueByPlan = (clone $paymentQ)->select('plan_id', DB::raw('sum(amount) as total'))
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
     * @param Carbon|null $start
     * @param Carbon|null $end
     */
    protected function getPlanChartData(?\Carbon\Carbon $start = null, ?\Carbon\Carbon $end = null): array
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->get();
        $labels = $plans->pluck('name')->toArray();

        $activeQ = UserSubscription::where('status', 'active');
        if ($start && $end) {
            $activeQ->where('start_date', '<=', $end->toDateString())
                ->where(function ($q) use ($start) {
                    $q->where('end_date', '>=', $start->toDateString())
                        ->orWhere('end_at', '>=', $start);
                });
        } else {
            $activeQ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNull('end_at')->where('end_date', '>=', now()->toDateString());
                })->orWhere(function ($q2) {
                    $q2->whereNotNull('end_at')->where('end_at', '>=', now());
                });
            });
        }
        $activeCounts = (clone $activeQ)->select('plan_id', DB::raw('count(*) as count'))
            ->groupBy('plan_id')
            ->pluck('count', 'plan_id');

        $revenueQ = PaymentTransaction::where('status', PaymentStatus::SUCCESS);
        if ($start && $end) {
            $revenueQ->whereBetween(DB::raw('COALESCE(paid_at, created_at)'), [$start, $end]);
        }
        $revenueByPlan = (clone $revenueQ)->select('plan_id', DB::raw('sum(amount) as total'))
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
