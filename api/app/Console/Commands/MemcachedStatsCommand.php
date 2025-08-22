<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MemcachedStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:memcached-stats-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mem = new \Memcached();
        $mem->addServer('memcached', 11211);
        $stats = $mem->getStats();

        if ($stats === false) {
            $this->error("Connection to memcached failed");
            return Command::FAILURE;
        }

        foreach ($stats as $server => $stat) {
            echo "=== Memcached Info for $server ===\n";
            echo "Uptime:          " . $stat['uptime'] . " sec\n";
            echo "Bytes Used:      " . $stat['bytes'] . "\n";
            echo "Memory Limit:    " . $stat['limit_maxbytes'] . "\n";
            echo "Items Stored:    " . $stat['curr_items'] . "\n";
            echo "Total Items:     " . $stat['total_items'] . "\n";
            echo "Cache Hits:      " . $stat['get_hits'] . "\n";
            echo "Cache Misses:    " . $stat['get_misses'] . "\n";
            echo "Evictions:       " . $stat['evictions'] . "\n";
            echo "=============================\n\n";
        }
    }
}
