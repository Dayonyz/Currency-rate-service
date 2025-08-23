<?php

namespace App\Services;

use App\Dto\CurrencyRateDto;
use App\Enums\CurrenciesEnum;
use App\Services\Contracts\CurrencyRates;
use DateTime;
use Exception;

class CurrencyRatesOpenExchange implements CurrencyRates
{
    private string $endpoint;
    private string $key;

    public function __construct()
    {
        $this->endpoint = config('services.open_exchange_rates.endpoint');
        $this->key = config('services.open_exchange_rates.key');
    }

    /**
     * @throws Exception
     */
    public function getCurrencyRateByDate(
        CurrenciesEnum $currency,
        CurrenciesEnum $baseCurrency,
        DateTime    $date
    ): CurrencyRateDto
    {
        $params = [
            'app_id' => $this->key,
            'base' => $baseCurrency->name,
            'symbols' => $currency->name
        ];

        $url = "{$this->endpoint}/historical/" .
               "{$date->format('Y-m-d')}.json?" .
               http_build_query($params);

        $response = $this->request($url);

        if (!isset($response['rates']))
            throw new Exception(json_encode($response));

        return (new CurrencyRateDto(
            $currency,
            $baseCurrency,
            $response['rates'][$currency->name],
            (new DateTime())->setTimestamp($response['timestamp'])
        ));
    }

    /**
     * @throws Exception
     */
    protected function request(string $url): array
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception("Invalid URL provided: $url");
        }

        $ch = curl_init($url);

        if ($ch === false) {
            throw new Exception("Failed to initialize cURL session.");
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Request error: $error", $httpCode);
        }

        curl_close($ch);

        return json_decode($response, true);
    }

}