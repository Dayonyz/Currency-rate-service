<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Psr\SimpleCache\InvalidArgumentException;
use SanctumBulwark\BulwarkTokenRepository;
use SanctumBulwark\PersonalAccessToken;
use SanctumBulwark\StaticContainer;

class CommonPerformanceTests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:common-performance-tests';

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

        $cacheService = app(BulwarkTokenRepository::class);
        StaticContainer::useTokenRepository($cacheService);

        $start = hrtime(true);
        for ($i = 0; $i < 100000; $i++) {
            $cacheService = app(BulwarkTokenRepository::class);
        }
        $end = hrtime(true);

        echo "Get instance from service container: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";


        $start = hrtime(true);
        for ($i = 0; $i < 100000; $i++) {
            $cacheService = StaticContainer::getTokenRepository();
        }
        $end = hrtime(true);

        echo "Get instance from static container: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        /**
         * @var PersonalAccessToken $token
         */
        $token = PersonalAccessToken::first();

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
            $token = StaticContainer::getTokenRepository()->getTokenWithProvider(12);
        }
        $end = hrtime(true);

        echo json_encode($token) . "\n";
        echo "Find from static container: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        for ($i = 0; $i < 10000; $i++) {
            $token = app(BulwarkTokenRepository::class)->getTokenWithProvider(12);
        }
        $end = hrtime(true);

        echo json_encode($token) . "\n";
        echo "Find from app container: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";
    }
}
