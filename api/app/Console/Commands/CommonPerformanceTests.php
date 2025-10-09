<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Psr\SimpleCache\InvalidArgumentException;
use SanctumBulwark\Bulwark;
use SanctumBulwark\PersonalAccessToken;
use SanctumBulwark\TokenRepository;

class CommonPerformanceTests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:common';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws InvalidArgumentException
     */
    public function handle()
    {
        echo "---------------------------" . "\n";

        $cacheService = app(TokenRepository::class);
        Bulwark::useTokenRepository($cacheService);

        $start = hrtime(true);
        for ($i = 0; $i < 100000; $i++) {
            $cacheService = app(TokenRepository::class);
        }
        $end = hrtime(true);

        echo "Get instance from service container: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        for ($i = 0; $i < 100000; $i++) {
            $cacheService = Bulwark::getTokenRepository();
        }
        $end = hrtime(true);

        echo "Get instance from static container: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        /**
         * @var PersonalAccessToken $token
         */
        $token = PersonalAccessToken::find(12);

        $start = hrtime(true);
        for ($i = 0; $i < 100000; $i++) {
            $encoded = implode('***', $token->getRawOriginal());
        }
        $end = hrtime(true);

        echo "implode array: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        for ($i = 0; $i < 100000; $i++) {
            $decoded = explode('***', $encoded);
        }
        $end = hrtime(true);

        echo "explode array: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        for ($i = 0; $i < 100000; $i++) {
            $serialized = serialize($token->getRawOriginal());
        }
        $end = hrtime(true);

        echo "Serialize array: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";


        $start = hrtime(true);
        for ($i = 0; $i < 100000; $i++) {
            $dateTime = $token->created_at->getTimestamp();
        }
        $end = hrtime(true);

        echo $dateTime . "\n";
        echo "Get dateTime attribute directly: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        for ($i = 0; $i < 100000; $i++) {
            $dateTime = strtotime($token->getRawOriginal('created_at'));
        }
        $end = hrtime(true);

        echo $dateTime . "\n";
        echo "Raw dateTime attribute and convert: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        for ($i = 0; $i < 100000; $i++) {
            $unSerialized = unserialize($serialized);
        }
        $end = hrtime(true);

        echo "unSerialize array: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        for ($i = 0; $i < 10000; $i++) {
            (new PersonalAccessToken)->forceFill($token->getRawOriginal());
        }
        $end = hrtime(true);

        echo "Create Model copy directly: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        $preparedToken = (new PersonalAccessToken)->setConnection(config('database.default'));
        $preparedToken->exists = true;

        $start = hrtime(true);
        for ($i = 0; $i < 10000; $i++) {
            (clone $preparedToken)->setRawAttributes($token->getRawOriginal())->syncOriginal();
        }
        $end = hrtime(true);

        echo "Create Model from prepared instance: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        for ($i = 0; $i < 10000; $i++) {
            $token = Bulwark::getTokenRepository()->getTokenWithProvider(12);
        }
        $end = hrtime(true);

        echo "Find from static container: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        for ($i = 0; $i < 10000; $i++) {
            $token = app(TokenRepository::class)->getTokenWithProvider(12);
        }
        $end = hrtime(true);

        echo "Find from app container: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        for ($i = 0; $i < 10000; $i++) {
            Redis::connection('cache')->hmset('test_token:' . 12, $token->getRawOriginal());
        }
        $end = hrtime(true);

        echo "Redis hmset  array: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        for ($i = 0; $i < 10000; $i++) {
            $result = Redis::connection('cache')->hgetall('test_token:' . 12);
        }
        $end = hrtime(true);

        echo "Redis hgetall  array: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        for ($i = 0; $i < 10000; $i++) {
            Bulwark::getTokenRepository()->storeToken($token);
        }
        $end = hrtime(true);

        echo "Redis serialize store array: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        for ($i = 0; $i < 10000; $i++) {
            Bulwark::getTokenRepository()->getToken($token->id);
        }
        $end = hrtime(true);

        echo "Redis unSerialize get array:" . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";
    }
}
