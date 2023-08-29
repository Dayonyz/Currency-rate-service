<?php

namespace App\Console\Commands;

use App\Dto\Collections\PairRateDtoCollection;
use App\Enums\CurrencyEnum;
use App\Helpers\EnumHelper;
use App\Services\Contracts\CurrencyRates;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FetchCurrencyRatesMonthly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-currency-rates-monthly {--currency=} {--base=} {--M=} {--Y=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(CurrencyRates $ratesService)
    {
        $collection = new PairRateDtoCollection();
        $now = new \DateTime('now');
        $since = new \DateTime("{$this->option('Y')}-{$this->option('M')}-01");
        $interval = new \DateInterval('P1D');
        $fileName = "{$this->option('currency')}-" .
            "{$this->option('base')}-" .
            "{$this->option('M')}-{$this->option('Y')}.json";

        try {
            while (
                $since->format('m') === $this->option('M') &&
                $since->diff($now)->days >= 1
            ) {
                $rate = $ratesService->getCurrencyRateByDate(
                    EnumHelper::caseByName(CurrencyEnum::cases(), $this->option('currency')),
                    EnumHelper::caseByName(CurrencyEnum::cases(), $this->option('base')),
                    $since
                );

                $collection->pushCurrencyRate($rate);

                $since->add($interval);
            }

            Storage::disk('public')->put(
                $fileName,
                $collection->getSerialized()
            );

            $this->line('DONE: ' . $fileName);
        } catch (\Exception $exception) {
            $this->error('FAIL: ' . $fileName . ', MESSAGE: ' . $exception->getMessage());
        }
    }
}
