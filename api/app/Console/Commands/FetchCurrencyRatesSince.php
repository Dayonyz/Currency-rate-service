<?php

namespace App\Console\Commands;

use DateInterval;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class FetchCurrencyRatesSince extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-currency-rates-since {--currency=} {--base=} {--M=} {--Y=}';

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
    public function handle(): int
    {
        $startDate = new DateTime("{$this->option('Y')}-{$this->option('M')}-01");
        $now = new DateTime('now');

        while ($startDate <= $now) {
            $params = "--currency={$this->option('currency')} " .
                "--base={$this->option('base')} " .
                "--M={$startDate->format('m')} " .
                "--Y={$startDate->format('Y')}";

            $result = Process::timeout(60)->run(
                "php " . base_path('artisan') . " app:fetch-currency-rates-monthly {$params}"
            );

            $this->line($result->output());

            $startDate->add(new DateInterval('P1M'));
        }

        $this->info('Currency rates fetching COMPLETED');

        return self::SUCCESS;
    }
}
