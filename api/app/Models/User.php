<?php

namespace App\Models;

use App\Services\CacheAccessTokensService;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Queue\SerializesModels;
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
        $version = (int) (microtime(true) * 1000000);
        $key = CacheAccessTokensService::getKey($plainTextToken);

        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
            'version' => $version,
            'key' => $key
        ]);

        $fullToken = $token->getKey() . '|' . $plainTextToken;

        if (config('sanctum.cache')) {
            app(CacheAccessTokensService::class)->store($key, $token, $this);
        }

        return new NewAccessToken($token, $fullToken);
    }
}
