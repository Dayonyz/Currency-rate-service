<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use SodiumException;

class HashPerformanceTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:hash';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws SodiumException
     */
    public function handle()
    {
        $tokens = [];

        for ($i = 0; $i < 100000; $i++) {
            $tokens[] = sprintf(
                '%s%s%s',
                config('sanctum.token_prefix', ''),
                $tokenEntropy = Str::random(40),
                hash('crc32b', $tokenEntropy)
            );
        }

        echo 'First token: ' . $tokens[0] . "\n";
        echo 'Token length: ' . strlen($tokens[0]) . "\n";
        echo 'App key:' . config('app.key') . "\n";
        echo 'App key length:' . strlen(config('app.key')) . "\n";
        echo "Current system is 64-bit: " . (int)(PHP_INT_SIZE >= 8 || PHP_INT_MAX > 0x7fffffff). "\n";
        echo "------------------------------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            $hash = sodium_bin2hex(sodium_crypto_generichash(
                $token,
                '',
                16
            ));
        }
        $end = hrtime(true);

        echo "Sodium 16: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "Last hash: " . $hash . "\n";
        echo "Hash length: " . strlen($hash) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            hash_equals($hash, sodium_bin2hex(sodium_crypto_generichash(
                $token,
                '',
                16
            )));
        }
        $end = hrtime(true);

        echo "Sodium 16 compare: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            $hash = sodium_bin2hex(sodium_crypto_generichash(
                $token,
                '',
                24
            ));
        }
        $end = hrtime(true);

        echo "Sodium 24: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "Last hash: " . $hash . "\n";
        echo "Hash length: " . strlen($hash) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            hash_equals($hash, sodium_bin2hex(sodium_crypto_generichash(
                $token,
                '',
                24
            )));
        }
        $end = hrtime(true);

        echo "Sodium 24 compare: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            $hash = sodium_bin2hex(sodium_crypto_generichash(
                $token,
                ''
            ));
        }
        $end = hrtime(true);

        echo "Sodium 32: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "Last hash: " . $hash . "\n";
        echo "Hash length: " . strlen($hash) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            hash_equals($hash, sodium_bin2hex(sodium_crypto_generichash(
                $token,
                ''
            )));
        }
        $end = hrtime(true);

        echo "Sodium 32 compare: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            $hash = hash('sha256', $token);
        }
        $end = hrtime(true);

        echo "sha256: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "Last hash: " . $hash . "\n";
        echo "Hash length: " . strlen($hash) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            $hash = hash('sha224', $token);
        }
        $end = hrtime(true);

        echo "sha224: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "Last hash: " . $hash . "\n";
        echo "Hash length: " . strlen($hash) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            $hash = hash('sha512/256', $token);
        }
        $end = hrtime(true);

        echo "sha512/256: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "Last hash: " . $hash . "\n";
        echo "Hash length: " . strlen($hash) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            $hash = hash('sha512/224', $token);
        }

        $end = hrtime(true);

        echo "sha512/224: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "Last hash: " . $hash . "\n";
        echo "Hash length: " . strlen($hash) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            $hash = hash('xxh3', $token);
        }
        $end = hrtime(true);

        echo "xxh3: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "Last hash: " . $hash . "\n";
        echo "Hash length: " . strlen($hash) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            $hash = hash('xxh32', $token);
        }
        $end = hrtime(true);

        echo "xxh32: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "Last hash: " . $hash . "\n";
        echo "Hash length: " . strlen($hash) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            $hash = hash('xxh64', $token);
        }
        $end = hrtime(true);

        echo "xxh64: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "Last hash: " . $hash . "\n";
        echo "Hash length: " . strlen($hash) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            $hash = hash('xxh128', $token);
        }
        $end = hrtime(true);

        echo "xxh128: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "Last hash: " . $hash . "\n";
        echo "Hash length: " . strlen($hash) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            $hash = hash('crc32b', $token);
        }
        $end = hrtime(true);

        echo "crc32b: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "Last hash: " . $hash . "\n";
        echo "Hash length: " . strlen($hash) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            $hash = md5(hash('xxh32', substr($token, -24)) . $token . hash('xxh32', $token));
        }
        $end = hrtime(true);

        echo "md5 + xxh32: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "Last hash:" . $hash . "\n";
        echo "Hash length: " . strlen($hash) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            $hash = sha1($token);
        }
        $end = hrtime(true);

        echo "sh1: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "Last hash: " . $hash . "\n";
        echo "Hash length: " . strlen($hash) . "\n";
        echo "---------------------------" . "\n";

        $start = hrtime(true);
        foreach ($tokens as $token) {
            $hash = md5($token);
        }
        $end = hrtime(true);

        echo "md5: " . round(($end-$start)/(1000*1000), 2) . "\n";
        echo "Last hash: " . $hash . "\n";
        echo "Hash length: " . strlen($hash) . "\n";
        echo "---------------------------" . "\n";
    }
}
