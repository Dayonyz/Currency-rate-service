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

    public PersonalAccessToken $token;
    public string $now;

    public function __construct(PersonalAccessToken $token, string $now)
    {
        $this->token = $token;
        $this->now = $now;
    }

    public function handle()
    {
        if (method_exists($this->token->getConnection(), 'hasModifiedRecords') &&
            method_exists($this->token->getConnection(), 'setRecordModificationState')) {
            $hasModifiedRecords = $this->token->getConnection()->hasModifiedRecords();
            $this->token->forceFill(['last_used_at' => $this->now])->save();

            $this->token->getConnection()->setRecordModificationState($hasModifiedRecords);
        } else {
            $this->token->forceFill(['last_used_at' => $this->now])->save();
        }
    }
}
