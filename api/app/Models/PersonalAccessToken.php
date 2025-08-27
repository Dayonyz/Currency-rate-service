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

        static::updating(function ($model) {
            if (config('sanctum.cache')) {
                $cachedVersion = Cache::driver(config('sanctum.cache'))
                    ->get('sanctum_auth_version:' . $model->key);

                if ($cachedVersion && $model->version >= $cachedVersion) {
                    $cacheValue = serialize($model);

                    if ($model->expires_at) {
                        Cache::driver(config('sanctum.cache'))
                            ->put(
                                'sanctum_auth:' . $model->key,
                                $cacheValue,
                                $model->expires_at
                            );
                    } else {
                        Cache::driver(config('sanctum.cache'))
                            ->rememberForever('sanctum_auth:' . $model->key, function () use ($cacheValue) {
                                return $cacheValue;
                            });
                    }
                } else {
                    return false;
                }
            }
        });

        static::deleting(function ($model) {
            if (config('sanctum.cache')) {
                Cache::driver(config('sanctum.cache'))->delete('sanctum_auth:' . $model->key);
                Cache::driver(config('sanctum.cache'))->delete('sanctum_auth_version:' . $model->key);
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
            $modelCacheKey = 'sanctum_auth_tokenable:' . $this->key;
            $modelData = Cache::driver(config('sanctum.cache'))->get($modelCacheKey);

            if ($modelData) {
                $instance = unserialize($modelData);

                if (is_object($instance) &&
                    $instance->id === $this->tokenable_id &&
                    $instance::class === $this->tokenable_type
                ) {
                    return $instance;
                }
            }
        }

        return parent::tokenable()->get();
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function findToken($token)
    {
        if (config('sanctum.cache')) {
            $key = hash('sha256', $token);
            $modelCacheKey = 'sanctum_auth:' . $key;
            $versionCacheKey = 'sanctum_auth_version:' . $key;

            $modelData = Cache::driver(config('sanctum.cache'))->get($modelCacheKey);

            if (!empty($modelData)) {
                $instance = unserialize($modelData);

                $getValidInstance = function () use ($token, $instance, $key) {
                    if (!str_contains($token, '|')) {
                        return $instance->token === $key ? $instance : null;
                    }

                    [$id, $token] = explode('|', $token, 2);

                    return $instance->id === intval($id) && hash_equals($instance->token, hash('sha256', $token)) ?
                        $instance :
                        null;
                };

                $instance = $getValidInstance();


                if ($instance) {
                    $version = (int)(microtime(true) * 1000000);

                    Cache::driver(config('sanctum.cache'))
                        ->put($versionCacheKey, $version);

                    $instance->version = $version;
                }

                return $instance;
            }
        }

        return parent::findToken($token);
    }
}