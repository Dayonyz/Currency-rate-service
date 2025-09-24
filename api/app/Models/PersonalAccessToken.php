<?php

namespace App\Models;

use App\Helpers\ContainerHelper;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\PersonalAccessToken as BaseToken;
use Psr\SimpleCache\InvalidArgumentException;
use SodiumException;

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
        'name',
        'token',
        'abilities',
        'expires_at',
        'version'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            ContainerHelper::getAccessTokenService()->deleteAccessTokenById($model->id);
        });
    }

    /**
     * @param $token
     * @return PersonalAccessToken|null
     * @throws InvalidArgumentException|SodiumException
     */
    public static function findToken($token): ?PersonalAccessToken
    {
        if (!str_contains($token, '|')) {
            return static::findTokenFromDB($token);
        }

        [$id, $plainTextToken] = explode('|', $token, 2);

        $accessToken = ContainerHelper::getAccessTokenService()->getAccessTokenWithProvider($id);

        if ($accessToken &&
            $accessToken->id === (int)$id &&
            hash_equals($accessToken->token, sodium_bin2hex(sodium_crypto_generichash(
                $plainTextToken,
                '',
                16
            )))
        ) {
            $accessToken->version = hrtime(true);

            ContainerHelper::getAccessTokenService()->storeAccessToken($accessToken);

            return $accessToken;
        }

        $accessToken = static::findTokenFromDB($token);

        if ($accessToken) {
            $accessToken->version = hrtime(true);
            ContainerHelper::getAccessTokenService()->storeAccessToken($accessToken);
        }

        return $accessToken;
    }

    /**
     * Find the token instance matching the given token.
     *
     * @param string $token
     * @return PersonalAccessToken|null
     * @throws SodiumException
     */
    public static function findTokenFromDB(string $token): ?static
    {
        if (!str_contains($token, '|')) {
            return static::where('token', sodium_bin2hex(sodium_crypto_generichash(
                $token,
                '',
                16
            )))->first();
        }

        [$id, $plainTextToken] = explode('|', $token, 2);

        if ($instance = static::find($id)) {
            return hash_equals($instance->token, sodium_bin2hex(sodium_crypto_generichash(
                $plainTextToken,
                '',
                16
            ))) ? $instance : null;
        }

        return null;
    }
}