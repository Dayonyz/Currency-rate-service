<?php

namespace App\Jobs;

use App\Models\PersonalAccessToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;

class TokenUpdateLastUsedAtJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public string $token;
    public string $now;

    public function __construct(string $token, string $now)
    {
        $this->token = $token;
        $this->now = $now;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handle()
    {
        $tokenModel = unserialize($this->token);

        if (method_exists($tokenModel->getConnection(), 'hasModifiedRecords') &&
            method_exists($tokenModel->getConnection(), 'setRecordModificationState')) {
            $hasModifiedRecords = $tokenModel->getConnection()->hasModifiedRecords();
            $this->saveToken($tokenModel);

            $tokenModel->getConnection()->setRecordModificationState($hasModifiedRecords);
        } else {
            $this->saveToken($tokenModel);
        }
    }

    private function saveToken(PersonalAccessToken $model): void
    {
        if (!config('sanctum.cache')) {
            $model->forceFill(['last_used_at' => $this->now])->save();

            return;
        }

        $jobTokenModel = $model;

        $redisTokenModelRaw = Cache::driver(config('sanctum.cache'))
            ->get('sanctum_auth:' . $jobTokenModel->key);

        if ($redisTokenModelRaw) {
            $redisTokenModel = unserialize($redisTokenModelRaw);

            if ($jobTokenModel->version >= $redisTokenModel->version) {
                $jobTokenModel->forceFill(['last_used_at' => $this->now])->save();
            }
        } else {
            $dbTokenModel = PersonalAccessToken::find($jobTokenModel->id);

            if ($dbTokenModel) {
                if ($jobTokenModel->version >= $dbTokenModel->version) {
                    $dbTokenModel->forceFill(['last_used_at' => $this->now])->save();
                }
            }
        }
    }
}
