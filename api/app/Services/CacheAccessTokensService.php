<?php

namespace App\Services;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Contracts\Cache\Repository as CacheInterface;
use Illuminate\Database\Eloquent\Model;
use Psr\SimpleCache\InvalidArgumentException;

class CacheAccessTokensService
{
    private CacheInterface $cache;
    private static PersonalAccessToken $preparedToken;
    private static User $preparedUser;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
        static::$preparedToken = (new PersonalAccessToken)->setConnection(config('database.default'));
        static::$preparedToken->exists = true;

        static::$preparedUser = (new User)->setConnection(config('database.default'));
        static::$preparedUser->exists = true;
    }

    public function storeAccessTokenAndProvider(PersonalAccessToken $token, Model $tokenAble): void
    {
        if ($token->expires_at) {
            $this->cache->put(
                "sanctum_auth:token:" . $token->id,
                serialize($token->getRawOriginal()),
                $token->expires_at
            );

            $this->cache->put(
                "sanctum_auth:tokenable:" . $token->id,
                serialize($tokenAble->getRawOriginal()),
                $token->expires_at
            );

        } else {
            $this->cache->forever(
                "sanctum_auth:token:" . $token->id,
                serialize($token->getRawOriginal())
            );

            $this->cache->forever(
                "sanctum_auth:tokenable:" . $token->id,
                serialize($tokenAble->getRawOriginal())
            );
        }
    }

    public function storeAccessTokenEloquent(PersonalAccessToken $token): void
    {
        $this->cache->forever(
            "sanctum_auth:token:db:" . $token->id,
            serialize(array_filter(
                $token->getChanges(),
                fn($k) => ! in_array($k, ['version', 'last_used_at']),
                ARRAY_FILTER_USE_KEY
            )),
        );
    }

    public function storeAccessToken(PersonalAccessToken $token): void
    {
        if ($token->expires_at) {
            $this->cache->put(
                "sanctum_auth:token:" . $token->id,
                serialize($token->getRawOriginal()),
                $token->expires_at
            );
        } else {
            $this->cache->forever(
                "sanctum_auth:token:" . $token->id,
                serialize($token->getRawOriginal())
            );
        }
    }

    public static function restoreAccessTokenFromRawOriginal(array $rawOriginal): PersonalAccessToken
    {
        return (clone static::$preparedToken)->setRawAttributes($rawOriginal)->syncOriginal();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getAccessTokenWithProvider(int $id): ?PersonalAccessToken
    {
        $rawOriginalToken = $this->cache->get("sanctum_auth:token:" . $id);
        $rawOriginalProvider = $this->cache->get("sanctum_auth:tokenable:" . $id);
        $tokenFromDb = $this->cache->get("sanctum_auth:token:db:" . $id);

        if (! $rawOriginalToken || ! $rawOriginalProvider) {
            return null;
        }

        if ($tokenFromDb) {
            $tokenFromDb = unserialize($tokenFromDb);
            $this->cache->delete("sanctum_auth:token:db:" . $id);

            return (clone static::$preparedToken)
                ->setRawAttributes(
                    array_merge(
                        array_filter(
                            unserialize($rawOriginalToken),
                            fn($k) => empty($tokenFromDb[$k]),
                            ARRAY_FILTER_USE_KEY
                        ),
                        $tokenFromDb
                    )
                )
                ->setRelation(
                    'tokenable',
                    (clone static::$preparedUser)->setRawAttributes(unserialize($rawOriginalProvider))->syncOriginal()
                )
                ->syncOriginal();
        }

        return (clone static::$preparedToken)->setRawAttributes(unserialize($rawOriginalToken))
            ->setRelation(
                'tokenable',
                (clone static::$preparedUser)->setRawAttributes(unserialize($rawOriginalProvider))->syncOriginal()
            )
            ->syncOriginal();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getAccessToken(int $id): ?PersonalAccessToken
    {
        $rawOriginal = $this->cache->get("sanctum_auth:token:" . $id);

        if (!$rawOriginal) {
            return null;
        }

        return (clone static::$preparedToken)->setRawAttributes(unserialize($rawOriginal))->syncOriginal();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function deleteAccessTokenById(int $id): void
    {
        $this->cache->delete("sanctum_auth:token:" . $id);
        $this->cache->delete("sanctum_auth:tokenable:"  .$id);
    }
}