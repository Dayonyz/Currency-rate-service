<?php

namespace App\Services\Contracts;

use App\Dto\CurrencyRateDto;
use App\Enums\CurrenciesEnum;

interface CurrencyRates
{
    public function getCurrencyRateByDate(
        CurrenciesEnum $currency,
        CurrenciesEnum $baseCurrency,
        \DateTime $date
    ): CurrencyRateDto;
}