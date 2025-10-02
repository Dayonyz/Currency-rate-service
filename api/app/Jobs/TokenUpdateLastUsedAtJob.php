<?php

namespace App\Jobs;

use App\Helpers\StaticContainer;
use App\Models\PersonalAccessToken;
use App\Services\SanctumCacheService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Psr\SimpleCache\InvalidArgumentException;

class TokenUpdateLastUsedAtJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public array $rawOriginal;
    public string $now;
    private SanctumCacheService $sanctumCacheService;

    public function __construct(array $rawOriginal, string $now)
    {
        $this->rawOriginal = $rawOriginal;
        $this->now = $now;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handle()
    {
        $this->sanctumCacheService = StaticContainer::getSanctumCacheService();
        $tokenModel = $this->sanctumCacheService::restoreTokenFromOriginal($this->rawOriginal);

        if (method_exists($tokenModel->getConnection(), 'hasModifiedRecords') &&
            method_exists($tokenModel->getConnection(), 'setRecordModificationState')) {
            $hasModifiedRecords = $tokenModel->getConnection()->hasModifiedRecords();
            $this->saveToken($tokenModel);

            $tokenModel->getConnection()->setRecordModificationState($hasModifiedRecords);
        } else {
            $this->saveToken($tokenModel);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function saveToken(PersonalAccessToken $jobTokenModel): void
    {
        $cacheTokenModel = $this->sanctumCacheService->getToken($jobTokenModel->id);

        if ($cacheTokenModel) {
            if ($jobTokenModel->getRawOriginal('version') >= $cacheTokenModel->getRawOriginal('version')) {
                $jobTokenModel->forceFill([
                    'last_used_at' => $this->now,
                ])->save();
            }
        } else {
            $dbTokenModel = PersonalAccessToken::find($jobTokenModel->id);

            if ($dbTokenModel) {
                $dbTokenModel->forceFill([
                    'last_used_at' => $this->now,
                ])->save();
            }
        }
    }
}