<?php

use App\Enums\CurrenciesEnum;
use App\Http\Controllers\AuthController;
use App\Repositories\EloquentCurrencyRatesRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrencyRatesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('currency/rates/{currency}/{baseCurrency}/{perPage}/{page?}', [CurrencyRatesController::class, 'index']);
    Route::get('currency/rate/{currency}/{baseCurrency}', [CurrencyRatesController::class, 'latest']);
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::get('/cache-test', function () {
    $value = app()->call(function () {
        return (new EloquentCurrencyRatesRepository(true))
            ->getLatestRate(CurrenciesEnum::EUR, CurrenciesEnum::USD);
    });

    return $value;
});

Route::get('/cache', function () {
    $value_store = Cache::remember('t_test', 1800, function ()  {
        return 'test_value';
    });

    $value_get = Cache::get('t_test');

    dd($value_store, $value_get);

});