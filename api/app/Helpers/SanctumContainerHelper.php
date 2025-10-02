<?php

namespace App\Helpers;

use App\Services\SanctumCacheService;

class SanctumContainerHelper
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