<?php

namespace App\Services;

use App\Models\PersonalAccessToken;
use Illuminate\Contracts\Cache\Repository as CacheInterface;
use Illuminate\Database\Eloquent\Model;
use Psr\SimpleCache\InvalidArgumentException;

class CacheAccessTokensService
{
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    protected static function getTokenPrefix(): string
    {
        return 'sanctum_auth:';
    }

    protected static function getTokenAblePrefix(): string
    {
        return 'sanctum_auth_tokenable:';
    }

    public static function getKey(string $plainTextToken): string
    {
        return sha1(config('app.key') . $plainTextToken);
    }

    protected static function sleep(mixed $model): string
    {
        return serialize($model);
    }

    protected static function wakeup(string $data): Model | PersonalAccessToken
    {
        return unserialize($data);
    }

    public function store(string $key, PersonalAccessToken $token, ?Model $provider = null): void
    {
        if ($token->expires_at) {
            $this->cache->put(
                static::getTokenPrefix() . $key,
                static::sleep($token),
                $token->expires_at
            );

            if ($provider) {
               $this->cache->put(
                    static::getTokenAblePrefix() . $key,
                    static::sleep($provider),
                    $token->expires_at
                );
            }

        } else {
            $this->cache->forever(
                static::getTokenPrefix() . $key,
                static::sleep($token)
            );

            if ($provider) {
                $this->cache->forever(
                    static::getTokenAblePrefix() . $key,
                    static::sleep($provider)
                );
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getAccessTokenByKey(string $key): ?PersonalAccessToken
    {
        $tokenKey = static::getTokenPrefix() . $key;
        $serializedModel = $this->cache->get($tokenKey);

        if (!$serializedModel) {
            return null;
        }

        return static::wakeup($serializedModel);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getTokenAbleByKey(string $key): ?Model
    {
        $tokenKey = static::getTokenAblePrefix() . $key;
        $serializedModel = $this->cache->get($tokenKey);

        if (!$serializedModel) {
            return null;
        }

        return static::wakeup($serializedModel);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function deleteAccessTokenByKey(string $key): void
    {
        $this->cache->delete(static::getTokenPrefix() . $key);
        $this->cache->delete(static::getTokenAblePrefix() . $key);
    }
}