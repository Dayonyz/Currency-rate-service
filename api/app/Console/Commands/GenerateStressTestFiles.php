<?php

namespace App\Console\Commands;

use App\Enums\CurrenciesEnum;
use App\Models\User;
use App\Repositories\Contracts\CurrencyRatesRepository;
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
    protected $description = 'Creates stress-test.xml for Tsung utility';

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

        for ($i= 0; $i < 1500; $i++) {
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
            'http://127.0.0.1:' . config('app.docker_nginx_port'),
            1000
        );
    }
}
