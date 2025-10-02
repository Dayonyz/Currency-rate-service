<?php

namespace App\Helpers;

use App\Services\SanctumCacheService;

class StaticContainer
{
    protected static ?SanctumCacheService $sanctumCacheService = null;

    public static function useSanctumCacheService(SanctumCacheService $service): void
    {
        static::$sanctumCacheService = $service;
    }

    public static function getSanctumCacheService(): SanctumCacheService
    {
        if (! static::$sanctumCacheService) {
            static::useSanctumCacheService(app(SanctumCacheService::class));
        }

        return static::$sanctumCacheService;
    }
}