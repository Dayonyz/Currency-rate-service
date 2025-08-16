<?php

namespace App\Dto\Collections;

use App\Dto\CurrencyRateDto;
use DateTime;
use Illuminate\Support\Collection;

class CurrencyRateDtoCollection
{
    private Collection $collection;

    public function __construct()
    {
        $this->collection = collect([]);
    }

    public function pushCurrencyRate(CurrencyRateDto $rateDto): void
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

    public function getLastFetchedDateTime(): ?DateTime
    {
        return $this->getPairRates()
            ->max(fn($item) => $item->getActualAt());
    }
}