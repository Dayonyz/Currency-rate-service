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
        ]);

        $fullToken = $token->getKey() . '|' . $plainTextToken;

        if (config('sanctum.cache')) {
            $redisKey = 'sanctum_auth:' . hash('sha256', $fullToken);

            $token->key = $redisKey;
            $token->save();

            $redisValue = serialize($token);

            if ($expiresAt) {
                Cache::driver(config('sanctum.cache'))->put($redisKey, $redisValue, $expiresAt);
            } else {
                Cache::driver(config('sanctum.cache'))->rememberForever($redisKey, function () use ($redisValue) {
                    return $redisValue;
                });
            }
        }

        return new NewAccessToken($token, $fullToken);
    }
}
