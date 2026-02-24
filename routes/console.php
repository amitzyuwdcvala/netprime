<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule subscription expiration check daily at midnight (Kolkata timezone)
Schedule::command('subscriptions:check-expiration')
    ->daily()
    ->at('00:00')
    ->timezone('Asia/Kolkata')
    ->withoutOverlapping()
    ->runInBackground();
