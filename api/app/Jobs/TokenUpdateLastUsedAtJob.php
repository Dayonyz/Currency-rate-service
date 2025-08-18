<?php

namespace App\Jobs;

use App\Models\PersonalAccessToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TokenUpdateLastUsedAtJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tokenId;
    public string $now;

    public function __construct(int $tokenId, string $now)
    {
        $this->tokenId = $tokenId;
        $this->now = $now;
    }

    public function handle()
    {
        $accessToken = PersonalAccessToken::find($this->tokenId);

        if ($accessToken) {
            if (method_exists($accessToken->getConnection(), 'hasModifiedRecords') &&
                method_exists($accessToken->getConnection(), 'setRecordModificationState')) {
                $hasModifiedRecords = $accessToken->getConnection()->hasModifiedRecords();
                $accessToken->forceFill(['last_used_at' => $this->now])->save();

                $accessToken->getConnection()->setRecordModificationState($hasModifiedRecords);
            } else {
                $accessToken->forceFill(['last_used_at' => $this->now])->save();
            }
        }
    }
}
