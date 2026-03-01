<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule subscription expiration check daily at midnight (Kolkata timezone).
// Ensures is_vip = false and subscription status = EXPIRED; does NOT reset video_click_count (remaining free videos persist).
// Run scheduler: php artisan schedule:work (dev) or add to crontab: * * * * * php /path/artisan schedule:run
Schedule::command('subscriptions:check-expiration')
    ->daily()
    ->at('00:00')
    ->timezone('Asia/Kolkata')
    ->withoutOverlapping();
