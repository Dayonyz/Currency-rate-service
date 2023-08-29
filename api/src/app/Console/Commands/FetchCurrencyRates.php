<?php

namespace App\Console\Commands;

use App\Dto\Collections\PairRateDtoCollection;
use App\Dto\PairRateDto;
use App\Enums\CurrencyEnum;
use App\Helpers\EnumHelper;
use App\Repositories\Contracts\CurrencyRatesRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class FetchCurrencyRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-currency-rates {--currency=} {--base=} {--M=} {--Y=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws \Exception
     */
    public function handle(CurrencyRatesRepository $currencyRatesRepository)
    {
        $startDate = new \DateTime("{$this->option('Y')}-{$this->option('M')}-01");
        $now = new \DateTime('now');
        $runProcesses = [];
        $files = [];
        $failed = false;

        while (true) {
            $params = "--currency={$this->option('currency')} " .
                "--base={$this->option('base')} " .
                "--M={$startDate->format('m')} " .
                "--Y={$startDate->format('Y')}";

            $files[] = "{$this->option('currency')}-" .
                "{$this->option('base')}-" .
                "{$startDate->format('m')}-" .
                "{$startDate->format('Y')}.json";

            $runProcesses[$params] = Process::timeout(0)->start(
                "php " . base_path('artisan') . " app:fetch-currency-rates-monthly {$params}"
            );

            if ($startDate->format('Y-m') === $now->format('Y-m'))
                break;

            $startDate->add(new \DateInterval('P1M'));
        }

        while (count($runProcesses)) {
            foreach ($runProcesses as $key => $process) {
                if (str_contains($process->output(), 'FAIL')) {
                    $this->error($process->output());
                    unset($runProcesses[$key]);
                    $failed = true;
                }

                if (str_contains($process->output(), 'DONE')) {
                    $this->info($process->output());
                    unset($runProcesses[$key]);
                }
            }
        }

        if ($failed) {
            $this->error("Process failed!");
            return;
        }

        $currencyRatesRepository->deletePairRatesByIso(
            EnumHelper::caseByName(CurrencyEnum::cases(), $this->option('currency')),
            EnumHelper::caseByName(CurrencyEnum::cases(), $this->option('base')),
        );

        foreach ($files as $file) {
            if (Storage::disk('public')->exists($file)) {
                /**
                 * @var $collection PairRateDtoCollection
                 */
                $collection = unserialize(Storage::disk('public')->get($file));

                foreach ($collection->getPairRates() as $pairRateDto) {
                    /**
                     * @var $pairRateDto PairRateDto
                     */
                    $currencyRatesRepository->storeRate($pairRateDto);
                }
            }
        }
    }
}
