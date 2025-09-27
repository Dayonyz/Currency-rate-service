<?php

namespace App\Helpers;

use App\Services\CacheAccessTokensService;

class TokensContainerHelper
{
    protected static ?CacheAccessTokensService $cacheAccessTokensService = null;

    public static function useCacheAccessTokensService(CacheAccessTokensService $tokensService): void
    {
        static::$cacheAccessTokensService = $tokensService;
    }

    public static function getAccessTokenService(): CacheAccessTokensService
    {
        if (! static::$cacheAccessTokensService) {
            static::useCacheAccessTokensService(app(CacheAccessTokensService::class));
        }

        return static::$cacheAccessTokensService;
    }
}