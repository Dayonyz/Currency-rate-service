<?php

namespace App\Repositories;

use App\Dto\CurrencyRateDto;
use App\Enums\CurrenciesEnum;
use App\Http\Resources\CurrencyRateResource;
use App\Models\Rate;
use App\Repositories\Contracts\CurrencyRatesRepository;
use ErrorException;
use Exception;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use Illuminate\Contracts\Cache\Repository as CacheInterface;

class EloquentCurrencyRatesRepository implements CurrencyRatesRepository
{
    private int $limit;
    private array $limits;
    private int $ttl;
    private ?CacheInterface $cache;

    public function __construct(?CacheInterface $cache)
    {
        $this->cache = $cache;
        $this->limits = config('repository.eloquent.limits');
        $this->limit = min($this->limits);
        $this->ttl = config('repository.eloquent.cache.ttl');
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
        $cacheKey = 'currency_rates:' . $currency->name . '_' . $baseCurrency->name . ':rate_latest';

        $getResponse = static fn() => CurrencyRateResource::make(
            Rate::byPairIso($currency, $baseCurrency)
                ->latest('id')
                ->first()
        )->toArray(request());

        if (! $this->cache) {
            return $getResponse();
        }

        return $this->cache->remember($cacheKey, $this->ttl, function () use ($getResponse, $cacheKey) {
            $rate = $getResponse();

            if (empty($rate)) {
                $this->cache->forget($cacheKey);
                return [];
            }

            return $rate;
        });
    }

    /**
     * @param CurrenciesEnum $currency
     * @param CurrenciesEnum $baseCurrency
     * @param int|null $limit
     * @param int|null $page
     * @return array
     */
    public function getAllRates(
        CurrenciesEnum $currency,
        CurrenciesEnum $baseCurrency,
        ?int $limit = null,
        ?int $page = null
    ): array {
        $this->normalizeLimit($limit);

        $page = $page ?? 1;
        $sort = 'desc';

        $query = Rate::byPairIso($currency, $baseCurrency)
            ->orderBy('id', $sort)
            ->orderBy('actual_at', $sort);

        if ($page > 1) {
            $query->offset(($page - 1) * $this->limit);
        }

        $query->limit($this->limit);

        $cacheKey = 'currency_rates:' .
            $currency->name . '_' . $baseCurrency->name .
            ':rates_paginate:' . $this->limit . '_' . $page;

        $getResponse = static fn() => CurrencyRateResource::collection($query->get())->toArray(request());

        if (! $this->cache) {
            return $getResponse();
        }

        return $this->cache->remember($cacheKey, $this->ttl, function () use ($getResponse, $cacheKey) {
            $rates = $getResponse();

            if (empty($rates)) {
                $this->cache->forget($cacheKey);
                return [];
            }

            return $rates;
        });
    }

    /**
     * @param CurrenciesEnum $currency
     * @param CurrenciesEnum $baseCurrency
     * @return int
     */
    public function getRatesTotalCount(CurrenciesEnum $currency, CurrenciesEnum $baseCurrency): int
    {
        $cacheKey = 'currency_rates:' . $currency->name . '_' . $baseCurrency->name . ':rates_count';

        $getResponse = static fn() => Rate::byPairIso($currency, $baseCurrency)->count();

        if (! $this->cache) {
            return $getResponse();
        }

        return $this->cache->remember($cacheKey, $this->ttl, function () use ($getResponse, $cacheKey) {
            $count = $getResponse();

            if ($count === 0) {
                $this->cache->forget($cacheKey);
            }

            return $count;
        });
    }

    private function normalizeLimit(?int $limit): void
    {
        if (is_null($limit) || $limit <= $this->limit) {
            return;
        }

        foreach ($this->limits as $allowedLimit) {
            if ($limit <= $allowedLimit) {
                $this->limit = $allowedLimit;

                return;
            }
        }

        $this->limit = max($this->limits);
    }
}