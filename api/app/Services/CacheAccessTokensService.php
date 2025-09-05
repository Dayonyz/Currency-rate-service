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
        return 'sanctum_auth:';
    }

    protected static function getTokenablePrefix(): string
    {
        return 'sanctum_auth_tokenable:';
    }

    public static function getKey(string $plainTextToken): string
    {
        return sha1(config('app.key') . $plainTextToken);
    }

    protected static function getTokenKey(string $plainTextToken): string
    {
        return static::getTokenPrefix() . static::getKey($plainTextToken);
    }

    protected static function getTokenableKey(string $plainTextToken): string
    {
        return static::getTokenablePrefix() . static::getKey($plainTextToken);
    }

    protected static function sleep(Model $model): string
    {
        return serialize($model);
    }

    protected static function wakeup(string $data): Model | PersonalAccessToken
    {
        return unserialize($data);
    }

    public static function store(string $plainTextToken, PersonalAccessToken $token, ?Model $provider = null): void
    {
        if ($token->expires_at) {
            Cache::driver(config('sanctum.cache'))->put(
                static::getTokenKey($plainTextToken),
                static::sleep($token),
                $token->expires_at
            );
            if ($provider) {
                Cache::driver(config('sanctum.cache'))->put(
                    static::getTokenableKey($provider),
                    static::sleep($provider),
                    $token->expires_at
                );
            }

        } else {
            Cache::driver(config('sanctum.cache'))->forever(
                static::getTokenKey($plainTextToken),
                static::sleep($token)
            );

            if ($provider) {
                Cache::driver(config('sanctum.cache'))->forever(
                    static::getTokenableKey($provider),
                    static::sleep($provider)
                );
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function getAccessTokenByToken(string $token): ?PersonalAccessToken
    {
        [$id, $plainTextToken] = explode('|', $token, 2);

        if (!$plainTextToken) {
            return null;
        }

        $serializedModel = Cache::driver(config('sanctum.cache'))->get(static::getTokenKey($plainTextToken));

        if (!$serializedModel) {
            return null;
        }

        $model = static::wakeup($serializedModel);

        if ($model->id !== $id) {
            return null;
        }

        return $model;
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