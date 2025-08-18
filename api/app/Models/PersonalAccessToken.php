<?php

namespace App\Models;

use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Queue\SerializesModels;
use Laravel\Sanctum\PersonalAccessToken as BaseToken;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;

class PersonalAccessToken extends BaseToken
{
    use SerializesModels, SerializesAndRestoresModelIdentifiers;
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
    ];

    /**
     * @throws InvalidArgumentException
     */
    public static function findToken($token)
    {
        $redisKey = 'sanctum_auth:' . hash('sha256', $token);

        $modelData = Cache::driver('redis')->get($redisKey);

        if (!empty($modelData)) {

            $instance = unserialize($modelData);

            if (!str_contains($token, '|')) {
                return $instance->token === hash('sha256', $token) ? $instance : null;
            }

            [$id, $token] = explode('|', $token, 2);

            return $instance->id === intval($id) && hash_equals($instance->token, hash('sha256', $token)) ?
                $instance :
                null;
        }

        return parent::findToken($token);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(): ?bool
    {
        Cache::driver('redis')->delete($this->key);

        return parent::delete();
    }
}