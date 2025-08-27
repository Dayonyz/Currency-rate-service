<?php

namespace App\Models;

use App\Enums\CurrenciesEnum;
use Illuminate\Database\Eloquent\Model;

class RatePageMarker extends Model
{
    protected $casts = [
        'currency_iso' => CurrenciesEnum::class,
        'base_currency_iso' => CurrenciesEnum::class
    ];

    protected $fillable = ['currency_iso', 'base_currency_iso', 'limit', 'page', 'since_rate_id'];
}
