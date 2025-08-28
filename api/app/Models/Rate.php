<?php

namespace App\Models;

use App\Enums\CurrenciesEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Rate extends Model
{
    protected $casts = [
        'currency_iso' => CurrenciesEnum::class,
        'base_currency_iso' => CurrenciesEnum::class
    ];

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
