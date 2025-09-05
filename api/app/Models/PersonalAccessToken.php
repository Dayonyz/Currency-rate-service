<?php

namespace App\Models;

use App\Services\CacheAccessTokensService;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\PersonalAccessToken as BaseToken;
use Psr\SimpleCache\InvalidArgumentException;

class PersonalAccessToken extends BaseToken
{
    use SoftDeletes;
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
                CacheAccessTokensService::deleteAccessTokenByKey($model->key);
            }
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getTokenableAttribute(): mixed
    {
        if (config('sanctum.cache')) {
            $instance = CacheAccessTokensService::getTokenableByKey($this->key);

            if ($instance &&
                $instance->id === $this->tokenable_id &&
                $instance::class === $this->tokenable_type
            ) {
                return $instance;
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
        $key = CacheAccessTokensService::getKey($plainTextToken);

        $instance = CacheAccessTokensService::getAccessTokenByKey($key);

        if ($instance && $instance->id === (int)$id) {
            $instance->version = (int)(microtime(true) * 1000000);
            CacheAccessTokensService::store($key, $instance);

            return $instance;
        }

        $instance = parent::findToken($token);

        if ($instance && $instance->id === (int)$id) {
            $instance->version = (int)(microtime(true) * 1000000);
            CacheAccessTokensService::store($key, $instance);
        }

        return $instance;
    }
}