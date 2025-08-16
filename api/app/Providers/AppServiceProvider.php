<?php

namespace App\Providers;

use App\Repositories\Contracts\CurrencyRatesRepository;
use App\Repositories\EloquentCurrencyRatesRepository;
use App\Services\Contracts\CurrencyRates;
use App\Services\CurrencyRatesOpenExchange;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CurrencyRates::class, CurrencyRatesOpenExchange::class);

        $this->app->singleton(CurrencyRatesRepository::class, function () {
            return new EloquentCurrencyRatesRepository(config('cache.repo_cache'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
