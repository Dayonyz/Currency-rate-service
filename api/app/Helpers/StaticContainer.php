<?php

namespace App\Helpers;

use App\Services\SanctumCacheService;

class StaticContainer
{
    public static ?SanctumCacheService $sanctumCache = null;

    public static function useSanctumCache(SanctumCacheService $service): void
    {
        static::$sanctumCache = $service;
    }

    public static function getSanctumCache(): SanctumCacheService
    {
        if (! static::$sanctumCache) {
            static::useSanctumCache(app(SanctumCacheService::class));
        }

        return static::$sanctumCache;
    }
}