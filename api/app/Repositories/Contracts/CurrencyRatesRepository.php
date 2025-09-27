<?php

namespace App\Repositories\Contracts;

use App\Dto\CurrencyRateDto;
use App\Enums\CurrenciesEnum;

interface CurrencyRatesRepository
{
    public function storeRate(CurrencyRateDto $rateDto);

    public function deleteAllRates(CurrenciesEnum $currency, CurrenciesEnum $baseCurrency);
    
    public function getLatestRate(CurrenciesEnum $currency, CurrenciesEnum $baseCurrency);
    
    public function getAllRates(
        CurrenciesEnum $currency,
        CurrenciesEnum $baseCurrency,
        ?int           $limit = null,
        ?int           $page = null
    );

    public function getRatesTotalCount(
        CurrenciesEnum $currency,
        CurrenciesEnum $baseCurrency,
    ): int;
}