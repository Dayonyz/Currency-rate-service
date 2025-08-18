<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SerializesModels, SerializesAndRestoresModelIdentifiers;

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

    public function createToken(
        string $name,
        array $abilities = ['*'],
        ?DateTimeInterface $expiresAt = null
    ): NewAccessToken {
        $plainTextToken = $this->generateTokenString();

        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
            'version' => (int) (microtime(true) * 1000000)
        ]);

        $fullToken = $token->getKey() . '|' . $plainTextToken;

        if (config('sanctum.cache')) {
            $key = hash('sha256', $fullToken);
            $modelCacheKey = 'sanctum_auth:' . $key;

            $token->key = $key;
            $token->save();

            $cacheValue = serialize($token);

            if ($expiresAt) {
                Cache::driver(config('sanctum.cache'))->put($modelCacheKey, $cacheValue, $expiresAt);
            } else {
                Cache::driver(config('sanctum.cache'))->rememberForever($modelCacheKey, function () use ($cacheValue) {
                    return $cacheValue;
                });
            }
        }

        return new NewAccessToken($token, $fullToken);
    }
}
