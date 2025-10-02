<?php

namespace App\Models;

use App\Helpers\SanctumContainerHelper;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use SodiumException;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected static function boot()
    {
        parent::boot();

        static::deleted(function (User $model) {
            $model->tokens()->delete();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @throws SodiumException
     */
    public function createToken(
        string $name,
        array $abilities = ['*'],
        ?DateTimeInterface $expiresAt = null
    ): NewAccessToken {
        $plainTextToken = $this->generateTokenString();

        $token = $this->tokens()->create([
            'name' => $name,
            'token' =>  sodium_bin2hex(sodium_crypto_generichash(
                $plainTextToken,
                '',
                16
            )),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        $fullToken = $token->id . '|' . $plainTextToken;

        if (config('sanctum.cache')) {
            SanctumContainerHelper::getSanctumCacheService()->storeTokenAndProvider($token, $this);
        }

        return new NewAccessToken($token, $fullToken);
    }
}
