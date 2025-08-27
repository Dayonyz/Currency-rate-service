<?php

namespace App\Http\Controllers;

use App\Enums\CurrenciesEnum;
use App\Repositories\Contracts\CurrencyRatesRepository;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CurrencyRatesController extends Controller
{
    private CurrencyRatesRepository $currencyRateRepository;

    public function __construct(CurrencyRatesRepository $currencyRateRepository)
    {
        $this->currencyRateRepository = $currencyRateRepository;
    }

    public function index(
        CurrenciesEnum $currency,
        CurrenciesEnum $baseCurrency,
        int $perPage,
        ?int $page = null
    ): JsonResponse {
        $itemsCount = $this->currencyRateRepository->getRatesTotalCount($currency, $baseCurrency);

        if ($itemsCount === 0) {
            return response()->json([
                'success' => false,
                'data' => [
                    'rates' => [],
                    'page' => $page,
                    'pagesCount' => 0,
                    'itemsCount' => $itemsCount
                ],
            ], Response::HTTP_OK);
        }

        $pagesCount = ceil($itemsCount / $perPage);

        if ($page > $pagesCount) {
            $page = $pagesCount;
        }

        $rates = $this->currencyRateRepository->getAllRates(
            $currency,
            $baseCurrency,
            $perPage,
            ($page - 1)*$perPage
        );

        return response()->json([
            'success' => ! empty($rates),
            'data' => [
                'rates' => $rates,
                'page' => $page,
                'pagesCount' => $pagesCount,
                'itemsCount' => $itemsCount
            ],
        ], Response::HTTP_OK);
    }

    public function latest(CurrenciesEnum $currency, CurrenciesEnum $baseCurrency): JsonResponse
    {
        $rate = $this->currencyRateRepository->getLatestRate($currency, $baseCurrency);

        return response()->json([
            'success' => (bool) $rate,
            'data' => $rate,
        ], Response::HTTP_OK);
    }
}
