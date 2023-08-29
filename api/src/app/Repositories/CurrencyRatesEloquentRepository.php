<?php

namespace App\Repositories;

use App\Dto\PairRateDto;
use App\Enums\CurrencyEnum;
use App\Exceptions\RepositoryNotFoundException;
use App\Models\Currency;
use App\Models\Rate;
use App\Repositories\Contracts\CurrencyRatesRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CurrencyRatesEloquentRepository implements CurrencyRatesRepository
{
    private bool $cacheable;

    public function __construct(bool $cacheable)
    {
        $this->cacheable = $cacheable;
    }

    /**
     * @throws \Exception
     */
    public function storeRate(PairRateDto $rateDto)
    {
        $rateModel = new Rate();
        $rateModel->currency()
                  ->associate(Currency::where([
                          'iso_code' => $rateDto->getCurrency()->name
                  ])->firstOrFail());

        $rateModel->baseCurrency()
                  ->associate(Currency::where([
                      'iso_code' => $rateDto->getBaseCurrency()->name
                  ])->firstOrFail());

        $rateModel->fill([
            'precision' => $rateDto->getPrecision(),
            'units' => $rateDto->getUnits(),
            'actual_at' => $rateDto->getActualAtDateTime()->format('Y-m-d H:i:s')
        ])->save();
    }

    public function deletePairRatesByIso(CurrencyEnum $currency, CurrencyEnum $base)
    {
        Rate::byPairIso($currency, $base)->delete();
    }

    /**
     * @throws \ErrorException
     */
    public function getLatestPairRateByIso(CurrencyEnum $currency, CurrencyEnum $base): Rate
    {
        $closure = function () use ($currency, $base) {
            return Rate::byPairIso($currency ,$base)
                ->with(['currency', 'baseCurrency'])
                ->orderBy('id','desc')
                ->first();
        };

        $rate = $this->cacheable ?
            $this->rememberOrRetrieve($closure, "{$currency->name}_{$base->name}_latest", ['rates']) :
            $closure();

        if (!$rate)
            throw new RepositoryNotFoundException('Resource not found');

        return $rate;
    }

    public function getPairRatesByIso(
        CurrencyEnum $currency,
        CurrencyEnum $base,
        ?int $perPage = null,
        int $page = 1
    ): Collection
    {
        $this->assertIsNullOrGreater($perPage, 0, 'Page size must be at least 1');
        $this->assertIsGreater($page, 0, 'Page must be at least 1');

        $closure = function () use ($currency, $base, $perPage, $page) {
            $query = Rate::byPairIso($currency ,$base)
                ->with(['currency', 'baseCurrency'])
                ->orderBy('id','desc');

            if ($perPage) {
                $query->offset(($page - 1)*$perPage)->limit($perPage);
            }

            return $query->get();
        };

        return $this->cacheable ?
            $this->rememberOrRetrieve($closure, "{$currency->name}_{$base->name}_{$perPage}_{$page}", ['rates']) :
            $closure();
    }

    public function getPairRatesPagesCount(CurrencyEnum $currency, CurrencyEnum $base, ?int $perPage = null): int
    {
        $this->assertIsNullOrGreater($perPage, 0, 'Page size must be at least 1');

        $closure = function () use ($currency, $base, $perPage) {
            return $perPage ? ceil(Rate::byPairIso($currency ,$base)->count() / $perPage) : 1;
        };

        return $this->cacheable ?
            $this->rememberOrRetrieve($closure, "COUNT_{$currency->name}_{$base->name}_{$perPage}", ['rates']) :
            $closure();
    }

    private function assertIsGreater(int $value, int $threshold, string $error): void
    {
        if ($value <= $threshold)
            throw new \InvalidArgumentException($error);
    }

    private function assertIsNullOrGreater(?int $value, int $threshold, string $error): void
    {
        if (!is_null($value) && $value <= $threshold)
            throw new \InvalidArgumentException($error);
    }

    private function rememberOrRetrieve(callable $closure, string $key, array $tags): Rate|Collection|int|null
    {
        $data = Cache::get($key);

        if (!$data) {
            $data = Cache::tags($tags)->rememberForever($key, $closure);
        }

        return $data;
    }
}