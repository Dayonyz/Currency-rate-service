<?php

namespace App\Providers;

use App\Repositories\Contracts\CurrencyRatesRepository;
use App\Repositories\CurrencyRatesEloquentRepository;
use App\Services\Contracts\CurrencyRates;
use App\Services\OpenExchangeRates;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CurrencyRates::class, OpenExchangeRates::class);

        $this->app->singleton(CurrencyRatesRepository::class, function () {
            return new CurrencyRatesEloquentRepository(config('cache.repo_cache'));
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
