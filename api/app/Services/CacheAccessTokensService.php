<?php

namespace App\Services;

use App\Models\PersonalAccessToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;

class CacheAccessTokensService
{
    protected static function getTokenPrefix(): string
    {
        return 'sanctum_token:';
    }

    protected static function getTokenablePrefix(): string
    {
        return 'sanctum_tokenable:';
    }

    public static function getKey(string $plainTextToken): string
    {
        return sha1(config('app.key') . $plainTextToken);
    }

    protected static function sleep(Model $model): string
    {
        return serialize($model);
    }

    protected static function wakeup(string $data): Model | PersonalAccessToken
    {
        return unserialize($data);
    }

    public static function store(string $key, PersonalAccessToken $token, ?Model $provider = null): void
    {
        if ($token->expires_at) {
            Cache::driver(config('sanctum.cache'))->put(
                static::getTokenPrefix() . $key,
                static::sleep($token),
                $token->expires_at
            );
            if ($provider) {
                Cache::driver(config('sanctum.cache'))->put(
                    static::getTokenablePrefix() . $key,
                    static::sleep($provider),
                    $token->expires_at
                );
            }

        } else {
            Cache::driver(config('sanctum.cache'))->forever(
                static::getTokenablePrefix() . $key,
                static::sleep($token)
            );

            if ($provider) {
                Cache::driver(config('sanctum.cache'))->forever(
                    static::getTokenablePrefix() . $key,
                    static::sleep($provider)
                );
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function getAccessTokenByKey(string $key): ?PersonalAccessToken
    {
        $tokenKey = static::getTokenPrefix() . $key;
        $serializedModel = Cache::driver(config('sanctum.cache'))->get($tokenKey);

        if (!$serializedModel) {
            return null;
        }

        return static::wakeup($serializedModel);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function getTokenableByKey(string $key): ?Model
    {
        $tokenKey = static::getTokenablePrefix() . $key;
        $serializedModel = Cache::driver(config('sanctum.cache'))->get($tokenKey);

        if (!$serializedModel) {
            return null;
        }

        return static::wakeup($serializedModel);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function deleteAccessTokenByKey(string $key): void
    {
        Cache::driver(config('sanctum.cache'))->delete(static::getTokenPrefix() . $key);
        Cache::driver(config('sanctum.cache'))->delete(static::getTokenablePrefix() . $key);
    }
}