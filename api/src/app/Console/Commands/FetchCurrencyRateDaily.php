<?php

namespace App\Console\Commands;

use App\Enums\CurrencyEnum;
use App\Helpers\EnumHelper;
use App\Repositories\Contracts\CurrencyRatesRepository;
use App\Services\Contracts\CurrencyRates;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FetchCurrencyRateDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-currency-rate-daily {--currency=} {--base=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(CurrencyRatesRepository $currencyRatesRepository, CurrencyRates $ratesService)
    {
        $now = new \DateTime('now');
        $now->sub(new \DateInterval('P1D'));

        try {
            $rate = $ratesService->getCurrencyRateByDate(
                EnumHelper::caseByName(CurrencyEnum::cases(), $this->option('currency')),
                EnumHelper::caseByName(CurrencyEnum::cases(), $this->option('base')),
                $now
            );

            $currencyRatesRepository->storeRate($rate);
            Cache::tags(['rates'])->clear();

        } catch (\Exception $exception) {
            Log::error('Daily currency rates fetching: ' . $exception->getMessage());
            $this->error('Daily currency rates fetching: ' . $exception->getMessage());
        }
    }
}
