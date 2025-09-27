<?php

namespace App\Models;

use App\Helpers\TokensContainerHelper;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\PersonalAccessToken as BaseToken;
use Psr\SimpleCache\InvalidArgumentException;
use SodiumException;

class PersonalAccessToken extends BaseToken
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'version',
        'expires_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($model) {
            TokensContainerHelper::getAccessTokenService()->deleteAccessTokenById($model->id);
        });
    }

    /**
     * @param $token
     * @return PersonalAccessToken|null
     * @throws InvalidArgumentException|SodiumException
     */
    public static function findToken($token): ?PersonalAccessToken
    {
        if (! str_contains($token, '|')) {
            return null;
        }

        [$id, $plainTextToken] = explode('|', $token, 2);

        $accessToken = TokensContainerHelper::getAccessTokenService()->getAccessTokenWithProvider($id);

        if ($accessToken &&
            $accessToken->id === (int)$id &&
            hash_equals($accessToken->token, sodium_bin2hex(sodium_crypto_generichash(
                $plainTextToken,
                '',
                16
            )))
        ) {
            $accessToken->version = hrtime(true);
            TokensContainerHelper::getAccessTokenService()->storeAccessToken($accessToken);

            return $accessToken;
        }

        $accessToken = static::findTokenFromDB($id, $plainTextToken);

        if ($accessToken) {
            $accessToken->version = hrtime(true);
            TokensContainerHelper::getAccessTokenService()->storeAccessToken($accessToken);
        }

        return $accessToken;
    }

    /**
     * Find the token instance matching the given token.
     *
     * @param int $id
     * @param string $plainTextToken
     * @return PersonalAccessToken|null
     * @throws SodiumException
     */
    public static function findTokenFromDB(int $id, string $plainTextToken): ?static
    {
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