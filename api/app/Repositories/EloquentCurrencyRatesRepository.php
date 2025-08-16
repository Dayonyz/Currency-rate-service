<?php

namespace App\Repositories;

use App\Dto\CurrencyRateDto;
use App\Enums\CurrenciesEnum;
use App\Http\Resources\CurrencyRateResource;
use App\Models\Rate;
use App\Repositories\Contracts\CurrencyRatesRepository;
use ErrorException;
use Exception;
use Illuminate\Support\Facades\Cache;
use JetBrains\PhpStorm\ArrayShape;
use Psr\SimpleCache\InvalidArgumentException;

class EloquentCurrencyRatesRepository implements CurrencyRatesRepository
{
    private int $limit;
    private bool $cacheable;

    public function __construct(bool $cacheable)
    {
        $this->limit = min(config('repository.eloquent.limits'));
        $this->cacheable = $cacheable;
    }

    /**
     * @throws Exception
     */
    public function storeRate(CurrencyRateDto $rateDto): void
    {
        Rate::updateOrCreate([
            'currency_iso' => $rateDto->getCurrency()->value,
            'base_currency_iso' => $rateDto->getBaseCurrency()->value,
            'actual_at' => $rateDto->getActualAt()->format('Y-m-d H:i:s')
        ], [
            'precision' => $rateDto->getPrecision(),
            'units' => $rateDto->getUnits(),
        ]);
    }

    public function deleteAllRates(CurrenciesEnum $currency, CurrenciesEnum $baseCurrency): bool
    {
        return Rate::byPairIso($currency, $baseCurrency)->delete();
    }

    /**
     * @throws ErrorException
     * @throws InvalidArgumentException
     */
    #[ArrayShape(['currency' => "array", 'base_currency' => "array", 'rate' => "mixed", 'actual_at' => "mixed"])]
    public function getLatestRate(CurrenciesEnum $currency, CurrenciesEnum $baseCurrency): ?array
    {
        if ($this->cacheable) {
            $cacheKey = "rate_latest:{$currency->name}_{$baseCurrency->name}";

            $rate = Cache::remember($cacheKey, 1800, function () use ($currency, $baseCurrency) {
                $latest = Rate::byPairIso($currency ,$baseCurrency)
                    ->latest('id')
                    ->first();

                return !$latest ?: CurrencyRateResource::make($latest)->toArray(request());
            });

            if (!$rate) {
                Cache::forget($cacheKey);

                return null;
            }
        }

        $rate = Rate::byPairIso($currency ,$baseCurrency)
            ->latest('id')
            ->first();

        return $rate ? CurrencyRateResource::make($rate)->toArray(request()) : null;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getAllRates(
        CurrenciesEnum $currency,
        CurrenciesEnum $baseCurrency,
        ?int           $limit = null,
        ?int           $offset = null
    ): array {
        $query = Rate::byPairIso($currency ,$baseCurrency)
            ->orderBy('actual_at','desc');

        $query->limit($this->normalizeLimit($limit));

        if (!is_null($offset)) {
            $query->offset($offset);
        }

        if ($this->cacheable) {
            $cacheKey = "rates_paginate:{$currency->name}_{$baseCurrency->name}_{$limit}_{$offset}";

            $rates = Cache::remember($cacheKey, 1800, function () use ($query) {
                return CurrencyRateResource::collection($query->get())->toArray(request());
            });

            if (count($rates) === 0) {
                Cache::forget($cacheKey);

                return [];
            }
        }

        return CurrencyRateResource::collection($query->get())->toArray(request());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getRatesTotalCount(CurrenciesEnum $currency, CurrenciesEnum $base): int
    {
        if ($this->cacheable) {
            $cacheKey = "rates_count:{$currency->name}_{$base->name}";

            $itemsCount = Cache::remember($cacheKey, 1800, function () use ($currency, $base) {
                return Rate::byPairIso($currency, $base)->count();
            });

            if ($itemsCount === 0) {
                Cache::forget($cacheKey);
            }

            return $itemsCount;
        }

        return Rate::byPairIso($currency, $base)->count();
    }

    private function normalizeLimit(?int $limit): int
    {
        if (is_null($limit) || $limit <= $this->limit) {
            return $this->limit;
        }

        $limits = config('repository.eloquent.limits');

        foreach ($limits as $allowedLimit) {
            if ($limit <= $allowedLimit) {
                $this->limit = $allowedLimit;

                return $this->limit;
            }
        }

        $this->limit = max($limits);

        return $this->limit;
    }
}