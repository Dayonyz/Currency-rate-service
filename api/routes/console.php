<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command("app:fetch-currency-rate-daily --currency=EUR --base=USD")
    ->dailyAt('00:01')
    ->withoutOverlapping()
    ->onFailure(function () {
        cache(['fetch_currency_failed' => true], 3600);
    })
    ->onSuccess(function () {
        cache()->forget('fetch_currency_failed');
    });

Schedule::command("app:fetch-currency-rate-daily --currency=EUR --base=USD")
    ->hourly()
    ->withoutOverlapping()
    ->when(fn() => cache('fetch_currency_failed', false))
    ->onSuccess(function () {
        cache()->forget('fetch_currency_failed');
    })
    ->onFailure(function () {
        $now = now();
        $secondsUntilMidnight = $now->diffInSeconds($now->copy()->endOfDay());

        cache(['fetch_currency_failed' => true], $secondsUntilMidnight);
    });
