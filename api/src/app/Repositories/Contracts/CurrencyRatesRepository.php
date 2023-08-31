<?php

namespace App\Repositories\Contracts;

use App\Dto\PaginationTotals;
use App\Dto\PairRateDto;
use App\Enums\CurrencyEnum;

interface CurrencyRatesRepository
{
    public function storeRate(PairRateDto $rateDto);

    public function deletePairRatesByIso(CurrencyEnum $currency, CurrencyEnum $base);
    
    public function getLatestPairRateByIso(CurrencyEnum $currency, CurrencyEnum $base);
    
    public function getPairRatesByIso(CurrencyEnum $currency, CurrencyEnum $base, ?int $perPage = null, int $page = 1);

    public function getPairRatesPaginationTotals(CurrencyEnum $currency, CurrencyEnum $base, ?int $perPage = null): PaginationTotals;
}