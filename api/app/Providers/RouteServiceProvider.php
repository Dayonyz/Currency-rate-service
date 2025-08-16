<?php

namespace App\Providers;

use App\Enums\CurrenciesEnum;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::bind('currency', function (string $value): CurrenciesEnum {
            return CurrenciesEnum::fromName($value);
        });

        Route::bind('baseCurrency', function (string $value): CurrenciesEnum {
            return CurrenciesEnum::fromName($value);
        });

        Route::bind('page', function (?string $value): int {
            $page = intval($value);
            return max($page, 1);
        });
    }
}
