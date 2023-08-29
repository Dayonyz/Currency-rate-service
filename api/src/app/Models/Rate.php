<?php

namespace App\Models;

use App\Enums\CurrencyEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Rate extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['precision', 'units', 'actual_at'];

    use HasFactory;

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function baseCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency_id', 'id');
    }

    public function scopeByPairIso(Builder $query, CurrencyEnum $currency, CurrencyEnum $base): void
    {
        $query->whereHas('currency', function ($query) use ($currency) {
            $query->where('iso_code', '=', $currency->name);
        })->whereHas('baseCurrency', function ($query) use ($base) {
            $query->where('iso_code', '=', $base->name);
        });
    }

    public function getValueAttribute(): float|int
    {
        return $this->units / pow(10, $this->precision);
    }
}
