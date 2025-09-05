<?php

namespace App\Console\Commands;

use App\Dto\Collections\CurrencyRateDtoCollection;
use App\Dto\CurrencyRateDto;
use App\Enums\CurrenciesEnum;
use App\Repositories\Contracts\CurrencyRatesRepository;
use App\Services\Contracts\CurrencyRates;
use DateInterval;
use DateTime;
use Exception;
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
    protected $description = 'Fetch currency rates monthly';

    private CurrencyRatesRepository $currencyRatesRepository;

    private CurrencyRates $ratesService;

    public function __construct(CurrencyRatesRepository $currencyRatesRepository, CurrencyRates $ratesService)
    {
        parent::__construct();
        $this->currencyRatesRepository = $currencyRatesRepository;
        $this->ratesService = $ratesService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $collection = new CurrencyRateDtoCollection();

        $now = new DateTime('now');
        $now->setTime(0, 0, 0);

        $since = new DateTime("{$this->option('Y')}-{$this->option('M')}-01");
        $since->setTime(0, 0, 0);

        $interval = new DateInterval('P1D');

        $filePath = "currencyRates/{$this->option('currency')}-" .
            "{$this->option('base')}-" .
            "{$this->option('M')}-{$this->option('Y')}.json";


        if (Storage::disk('public')->exists($filePath)) {
            $collection = unserialize(Storage::disk('public')->get($filePath));
        }

        $isCurrentMonth = $now->format('Y-m') === "{$this->option('Y')}-{$this->option('M')}";

        if (!$isCurrentMonth) {

            $this->saveToRepository($collection);

            $this->line('DONE: ' . $filePath);

            return self::SUCCESS;
        } else {
            if ($collection->getPairRates()->isNotEmpty()) {
                $since = $collection->getLastFetchedDateTime();
                $since->add($interval);
                $since->setTime(0, 0, 0);
            }
            try {
                while ($since <= $now) {
                    $rate = $this->ratesService->getCurrencyRateByDate(
                        CurrenciesEnum::fromName($this->option('currency')),
                        CurrenciesEnum::fromName($this->option('base')),
                        $since
                    );

                    $collection->pushCurrencyRate($rate);

                    $since->add($interval);
                }

                $this->saveToFile($collection, $filePath);

                $this->saveToRepository($collection);

                $this->line('DONE: ' . $filePath);

                return self::SUCCESS;

            } catch (Exception $exception) {

                $this->saveToFile($collection, $filePath);

                $this->saveToRepository($collection);

                $this->error("FAILED: store rate {$this->option('currency')}-{$this->option('base')}" .
                    ' at:'. $since->format('Y-m-d H:i:s') .
                    ', file:' . $filePath .
                    ', MESSAGE: ' . $exception->getMessage()
                );

                return self::FAILURE;
            }
        }
    }

    private function saveToRepository(CurrencyRateDtoCollection $collection): void
    {
        if ($collection->getPairRates()->isNotEmpty()) {
            foreach ($collection->getPairRates() as $pairRateDto) {
                /**
                 * @var $pairRateDto CurrencyRateDto
                 */
                $this->currencyRatesRepository->storeRate($pairRateDto);
            }
        }
    }

    private function saveToFile(CurrencyRateDtoCollection $collection, string $path): void
    {
        if ($collection->getPairRates()->isNotEmpty()) {
            Storage::disk('public')->put($path, $collection->getSerialized());
        }
    }
}
