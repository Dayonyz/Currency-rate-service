<?php

namespace App\Models;

use App\Services\CacheAccessTokensService;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Queue\SerializesModels;
use Laravel\Sanctum\PersonalAccessToken as BaseToken;
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
                app(CacheAccessTokensService::class)->deleteAccessTokenByKey($model->key);
            }
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getTokenableAttribute(): mixed
    {
        if (config('sanctum.cache')) {
            $instance = app(CacheAccessTokensService::class)->getTokenAbleByKey($this->key);

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

        $cacheService = app(CacheAccessTokensService::class);
        /**
         * @var CacheAccessTokensService $cacheService
         */
        $instance = $cacheService->getAccessTokenByKey($key);

        if ($instance && $instance->id === (int)$id) {
            $instance->version = (int)(microtime(true) * 1000000);
            $cacheService->store($key, $instance);

            return $instance;
        }

        $instance = parent::findToken($token);

        if (config('sanctum.cache') && $instance) {
            $instance->version = (int)(microtime(true) * 1000000);
            $cacheService->store($key, $instance);
        }

        return $instance;
    }
}