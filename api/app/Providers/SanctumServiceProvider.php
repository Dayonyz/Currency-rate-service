<?php

namespace App\Providers;

use App\Auth\SanctumCacheGuard;
use App\Models\PersonalAccessToken;
use App\Services\CacheAccessTokensService;
use Illuminate\Auth\RequestGuard;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class SanctumServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (config('sanctum.cache')) {
            Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
            $this->configureGuard();

            $this->app->singleton(CacheAccessTokensService::class, function () {
                return new CacheAccessTokensService(Cache::store(config('sanctum.cache')));
            });
        }
    }

    protected function configureGuard()
    {
        Auth::resolved(function ($auth) {
            $auth->extend('sanctum', function ($app, $name, array $config) use ($auth) {
                return tap($this->createGuard($auth, $config), function ($guard) {
                    app()->refresh('request', $guard, 'setRequest');
                });
            });
        });
    }

    /**
     * Register the guard.
     *
     * @param Factory $auth
     * @param array $config
     * @return RequestGuard
     */
    protected function createGuard(Factory $auth, array $config): RequestGuard
    {
        return new RequestGuard(
            new SanctumCacheGuard($auth, config('sanctum.expiration'), $config['provider']),
            request(),
            $auth->createUserProvider($config['provider'] ?? null)
        );
    }
}