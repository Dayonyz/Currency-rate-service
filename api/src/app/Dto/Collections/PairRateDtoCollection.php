<?php

namespace App\Dto\Collections;

use App\Dto\PairRateDto;
use Illuminate\Support\Collection;

class PairRateDtoCollection
{
    private Collection $collection;

    public function __construct()
    {
        $this->collection = collect([]);
    }

    public function pushCurrencyRate(PairRateDto $rateDto): void
    {
        $this->collection->push($rateDto);
    }

    public function getPairRates(): Collection
    {
        return $this->collection;
    }

    public function getSerialized(): string
    {
        return serialize($this);
    }
}