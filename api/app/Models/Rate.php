<?php

namespace App\Models;

use App\Enums\CurrenciesEnum;
use App\Repositories\EloquentCurrencyRatesRepository;
use App\Services\PageMarkerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class Rate extends Model
{
    protected $casts = [
        'currency_iso' => CurrenciesEnum::class,
        'base_currency_iso' => CurrenciesEnum::class
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::created(function ($model) {
            $cacheDriver = config('repository.eloquent.cache.driver');

            if ($cacheDriver) {
                $repository = new EloquentCurrencyRatesRepository(null);

                $index = $repository->getRatesTotalCount($model->currency_iso, $model->base_currency_iso);
                $pages = PageMarkerService::getPageByLimits($index, config('repository.eloquent.limits'));

                foreach ($pages as $limit => $page) {
                    if (PageMarkerService::isFirstItemOnPage($index, $limit, $page)) {
                        $cacheKey = "{$model->currency_iso->name}_{$model->base_currency_iso->name}:" .
                            "page_marker_{$limit}_{$page}";

                        Cache::driver(config('repository.eloquent.cache.driver'))->remember(
                            $cacheKey,
                            config('repository.eloquent.cache.ttl'),
                            fn() => $model->id
                        );
                    }
                }
            }
        });
    }

    const UPDATED_AT = null;

    protected $fillable = ['currency_iso', 'base_currency_iso', 'precision', 'units', 'actual_at'];

    public function scopeByPairIso(Builder $query, CurrenciesEnum $currency, CurrenciesEnum $base): void
    {
        $query->where('currency_iso', '=', $currency->value)
            ->where('base_currency_iso', '=', $base->value);
    }

    public function getValueAttribute(): float|int
    {
        return $this->units / pow(10, $this->precision);
    }
}
