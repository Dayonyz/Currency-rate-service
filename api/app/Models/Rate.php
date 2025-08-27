<?php

namespace App\Models;

use App\Enums\CurrenciesEnum;
use App\Repositories\Contracts\CurrencyRatesRepository;
use App\Repositories\EloquentCurrencyRatesRepository;
use App\Services\PageMarkerService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
            $createdMarkerIds = [];
            /**
             * @throws Exception
             */
            try {
                $repository = new EloquentCurrencyRatesRepository(null);

                $index = $repository->getRatesTotalCount($model->currency_iso, $model->base_currency_iso);
                $pages = PageMarkerService::getPagesByLimits($index, config('repository.eloquent.limits'));

                foreach ($pages as $limit => $page) {
                    if (PageMarkerService::isFirstItemOnPage($index, $limit, $page)) {
                        $createdMarkerIds[] = RatePageMarker::create([
                            'currency_iso' => $model->currency_iso->value,
                            'base_currency_iso' => $model->base_currency_iso->value,
                            'limit' => $limit,
                            'page' => $page,
                            'since_rate_id' => $model->id,
                        ])?->id;
                    }
                }
            } catch (Exception $exception) {
                DB::transaction(function() use ($model, $createdMarkerIds) {
                    $model->delete();
                    RatePageMarker::query()->whereIn('id', $createdMarkerIds)->delete();
                });

                throw $exception;
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
