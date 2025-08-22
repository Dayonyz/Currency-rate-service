<?php

use App\Http\Controllers\AuthController;
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

Route::get('/apc-test', function () {
    $info = apcu_cache_info(true);
    $mem = apcu_sma_info(true);

    echo "=== APCu Info ===\n";
    echo "Num Entries: " . $info['num_entries'] . "\n";
    echo "Num Hits:    " . $info['num_hits'] . "\n";
    echo "Num Misses:  " . $info['num_misses'] . "\n";
    echo "Memory Size: " . $mem['seg_size'] . "\n";
    echo "Avail Mem:   " . $mem['avail_mem'] . "\n";
    echo "================\n\n";
});