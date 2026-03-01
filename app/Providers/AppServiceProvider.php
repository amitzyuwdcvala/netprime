<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Payment API: 10 create-order + 10 verify per minute per user (by android_id) to handle spikes and prevent abuse
        RateLimiter::for('payment', function (Request $request) {
            $user = $request->user();
            $key = $user && $user->android_id
                ? 'payment:android:' . $user->android_id
                : 'payment:ip:' . $request->ip();

            return Limit::perMinute(10)->by($key);
        });

        // Video access API: 60 per minute per user to allow normal use while limiting spam under load
        RateLimiter::for('video_access', function (Request $request) {
            $user = $request->user();
            $key = $user && $user->android_id
                ? 'video_access:android:' . $user->android_id
                : 'video_access:ip:' . $request->ip();

            return Limit::perMinute(60)->by($key);
        });
    }
}
