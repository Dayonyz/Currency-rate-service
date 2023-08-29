<?php

namespace App\Services\Contracts;

use App\Dto\PairRateDto;
use App\Enums\CurrencyEnum;

interface CurrencyRates
{
    public function getCurrencyRateByDate(CurrencyEnum $currency, CurrencyEnum $baseCurrency, \DateTime $date): PairRateDto;
}