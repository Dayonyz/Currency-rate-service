<?php

namespace App\Jobs;

use App\Helpers\TokensContainerHelper;
use App\Models\PersonalAccessToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Psr\SimpleCache\InvalidArgumentException;

class TokenUpdateLastUsedAtJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public array $rawOriginal;
    public string $now;

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
        $tokenModel = TokensContainerHelper::getAccessTokenService()->restoreAccessTokenFromRawOriginal($this->rawOriginal);

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
        $cacheTokenModel = TokensContainerHelper::getAccessTokenService()->getAccessTokenInstance($jobTokenModel->id);

        if ($cacheTokenModel) {
            if ($jobTokenModel->version >= $cacheTokenModel->version) {
                $jobTokenModel->forceFill([
                    'last_used_at' => $this->now,
                    'version' => $jobTokenModel->version
                ])->save();
            }
        } else {
            $dbTokenModel = PersonalAccessToken::find($jobTokenModel->id);

            if ($dbTokenModel) {
                if ($jobTokenModel->version >= $dbTokenModel->version) {
                    $jobTokenModel->forceFill([
                        'last_used_at' => $this->now,
                        'version' => $jobTokenModel->version
                    ])->save();
                }
            }
        }
    }
}