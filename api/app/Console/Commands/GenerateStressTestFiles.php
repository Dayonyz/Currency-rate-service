<?php

namespace App\Console\Commands;

use App\Enums\CurrenciesEnum;
use App\Models\User;
use App\Repositories\Contracts\CurrencyRatesRepository;
use App\Services\CacheAccessTokensService;
use App\Services\K6StressTestService;
use Illuminate\Console\Command;

class GenerateStressTestFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-stress-test-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates load_test.js for Grafana K6 utility';

    private CurrencyRatesRepository $currencyRatesRepository;

    public function __construct(CurrencyRatesRepository $currencyRatesRepository)
    {
        parent:: __construct();
        $this->currencyRatesRepository = $currencyRatesRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tokens = [];

        for ($i= 0; $i < 5400; $i++) {
            $tokens[] = User::factory()->create([
                'name' => "Test user {$i}",
                'email' => "test{$i}@example.com",
            ])->createToken(config('app.name'))->plainTextToken;
        }

        $limits = config('repository.eloquent.limits');

        $ratesCount = $this->currencyRatesRepository->getRatesTotalCount(
            CurrenciesEnum::EUR,
            CurrenciesEnum::USD
        );

        if ($ratesCount === 0) {
            $this->line('No rates data parsed');

            return self::FAILURE;
        }

        $pageSizes = [];

        foreach ($limits as $limit) {
            $pageSizes[] = ['size' => $limit, 'maxPage' => ceil($ratesCount/$limit)];
        }

        K6StressTestService::generateStressTestFile(
            $tokens,
            $pageSizes,
            config('app.url'),
            1800
        );
    }
}
