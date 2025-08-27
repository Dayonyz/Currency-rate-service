<?php

namespace App\Repositories;

use App\Dto\CurrencyRateDto;
use App\Enums\CurrenciesEnum;
use App\Http\Resources\CurrencyRateResource;
use App\Models\Rate;
use App\Repositories\Contracts\CurrencyRatesRepository;
use ErrorException;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Psr\SimpleCache\InvalidArgumentException;
use Illuminate\Contracts\Cache\Repository as CacheInterface;

class EloquentCurrencyRatesRepository implements CurrencyRatesRepository
{
    private int $limit;
    private ?CacheInterface $cache;

    public function __construct(?CacheInterface $cache)
    {
        $this->limit = min(config('repository.eloquent.limits'));

        $this->cache = $cache;
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
        if ($this->cache) {
            $cacheKey = "{$currency->name}_{$baseCurrency->name}:rate_latest";

            $rate = $this->cache->remember(
                $cacheKey,
                config('repository.eloquent.cache.ttl'),
                function () use ($currency, $baseCurrency) {
                    $latest = Rate::byPairIso($currency ,$baseCurrency)
                        ->latest('id')
                        ->first();

                    return CurrencyRateResource::make($latest)->toArray(request());
                });

            if (empty($rate)) {
                $this->cache->forget($cacheKey);

                return [];
            } else {
                return $rate;
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
        ?int           $page = null
    ): array {
        $query = Rate::byPairIso($currency ,$baseCurrency)
            ->orderBy('id', 'desc')
            ->orderBy('actual_at', 'desc');

        $pageFromNull = $page ?: 1;
        $cacheKey = "{$currency->name}_{$baseCurrency->name}:page_marker_{$limit}_{$pageFromNull}";
        $pageMarkerId = $this->cache?->get($cacheKey);

        if ($pageMarkerId) {
            $query->where('id', '>=', $pageMarkerId);
        }

        $query->limit($this->normalizeLimit($limit));

        if (is_null($pageMarkerId) && $pageFromNull > 1) {
            $query->offset(($page - 1)*$limit);
        }

        if ($this->cache) {
            $cacheKey = "{$currency->name}_{$baseCurrency->name}:rates_paginate_{$limit}_{$page}";

            $rates = $this->cache->remember(
                $cacheKey,
                config('repository.eloquent.cache.ttl'),
                function () use ($query) {
                    return CurrencyRateResource::collection($query->get())->toArray(request());
                });

            if (empty($rates)) {
                $this->cache->forget($cacheKey);

                return [];
            } else {
                return $rates;
            }
        }

        return CurrencyRateResource::collection($query->get())->toArray(request());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getRatesTotalCount(CurrenciesEnum $currency, CurrenciesEnum $base): int
    {
        if ($this->cache) {
            $cacheKey = "{$currency->name}_{$base->name}:rates_count";

            $itemsCount = $this->cache->remember(
                $cacheKey,
                config('repository.eloquent.cache.ttl'),
                function () use ($currency, $base) {
                    return Rate::byPairIso($currency, $base)->count();
                });

            if ($itemsCount === 0) {
                $this->cache->forget($cacheKey);
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