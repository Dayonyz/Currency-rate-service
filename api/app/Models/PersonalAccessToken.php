<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Queue\SerializesModels;
use Laravel\Sanctum\PersonalAccessToken as BaseToken;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;

class PersonalAccessToken extends BaseToken
{
    use SerializesModels, SerializesAndRestoresModelIdentifiers, SoftDeletes;
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'name',
        'token',
        'abilities',
        'expires_at',
        'version'
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($model) {
            if (config('sanctum.cache')) {
                Cache::driver(config('sanctum.cache'))->delete('sanctum_auth:' . $model->key);
                Cache::driver(config('sanctum.cache'))->delete('sanctum_auth_tokenable:' . $model->key);
            }
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getTokenableAttribute(): mixed
    {
        if (config('sanctum.cache')) {
            $tokenableCacheKey = 'sanctum_auth_tokenable:' . $this->key;
            $tokenableSerialized = Cache::driver(config('sanctum.cache'))->get($tokenableCacheKey);

            if ($tokenableSerialized) {
                $instance = unserialize($tokenableSerialized);

                if (is_object($instance) &&
                    $instance->id === $this->tokenable_id &&
                    $instance::class === $this->tokenable_type
                ) {
                    return $instance;
                }
            }
        }

        return parent::tokenable()->first();
    }

    /**
     * @param $token
     * @return PersonalAccessToken|null
     * @throws InvalidArgumentException
     */
    public static function findToken($token): ?PersonalAccessToken
    {
        if (!config('sanctum.cache') || !str_contains($token, '|')) {
            return parent::findToken($token);
        }

        [$id, $plainTextToken] = explode('|', $token, 2);

        $key = sha1(config('app.key') . $plainTextToken);
        $cacheKey = 'sanctum_auth:' . $key;

        $cachedSerializedToken = Cache::driver(config('sanctum.cache'))->get($cacheKey);

        if ($cachedSerializedToken) {
            $instance = unserialize($cachedSerializedToken);

            if ($instance instanceof self && $instance->id === (int)$id) {
                $instance->version = (int)(microtime(true) * 1000000);
                if ($instance->expires_at) {
                    Cache::driver(config('sanctum.cache'))->put(
                        $cacheKey,
                        serialize($instance),
                        $instance->expires_at
                    );
                } else {
                    Cache::driver(config('sanctum.cache'))->forever($cacheKey, serialize($instance));
                }

                return $instance;
            }
        }

        $instance = parent::findToken($token);

        if (config('sanctum.cache') && $instance) {
            $instance->version = (int)(microtime(true) * 1000000);
            $serializedForCache = serialize($instance);

            if ($instance->expires_at) {
                Cache::driver(config('sanctum.cache'))->put($cacheKey, $serializedForCache, $instance->expires_at);
            } else {
                Cache::driver(config('sanctum.cache'))->forever($cacheKey, $serializedForCache);
            }
        }

        return $instance;
    }
}