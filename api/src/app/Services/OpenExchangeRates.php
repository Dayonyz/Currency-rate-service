<?php

namespace App\Services;

use App\Dto\PairRateDto;
use App\Enums\CurrencyEnum;
use App\Services\Contracts\CurrencyRates;
use Illuminate\Support\Facades\Log;

class OpenExchangeRates implements CurrencyRates
{
    private string $endpoint;
    private string $key;

    public function __construct()
    {
        $this->endpoint = config('services.open_exchange_rates.endpoint');
        $this->key = config('services.open_exchange_rates.key');
    }


    /**
     * @throws \Exception
     */
    public function getCurrencyRateByDate(
        CurrencyEnum $currency,
        CurrencyEnum $baseCurrency,
        \DateTime    $date
    ): PairRateDto
    {
        $url = "{$this->endpoint}/historical/" .
               "{$date->format('Y-m-d')}.json" .
               "?app_id={$this->key}&" .
               "base={$baseCurrency->name}&" .
               "symbols={$currency->name}";

        $response = $this->request($url);

        if (!isset($response['rates']))
            throw new \Exception(json_encode($response));

        return (new PairRateDto(
            $currency,
            $baseCurrency,
            $response['rates'][$currency->name],
            (new \DateTime())->setTimestamp($response['timestamp'])
        ));
    }


    /**
     * @throws \Exception
     */
    private function request(string $url): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $json = curl_exec($ch);
        curl_close($ch);

        if (!$json)
            throw new \Exception("Invalid url request: $url");

        return json_decode($json, true);
    }

}