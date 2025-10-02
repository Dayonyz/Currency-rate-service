<?php

namespace App\Providers;

use App\Helpers\SanctumContainerHelper;
use App\Repositories\Contracts\CurrencyRatesRepository;
use App\Repositories\EloquentCurrencyRatesRepository;
use App\Services\SanctumCacheService;
use App\Services\Contracts\CurrencyRates;
use App\Services\CurrencyRatesOpenExchange;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
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
            $repoCacheStore = config('repository.eloquent.cache.store');

            return new EloquentCurrencyRatesRepository(
                !$repoCacheStore ? null : Cache::store($repoCacheStore)
            );
        });
    }

    /**
     * Bootstrap any application services.
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        SanctumContainerHelper::useSanctumCacheService($this->app->make(SanctumCacheService::class));
    }
}
