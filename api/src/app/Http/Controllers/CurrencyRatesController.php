<?php

namespace App\Http\Controllers;

use App\Enums\CurrencyEnum;
use App\Helpers\EnumHelper;
use App\Http\Resources\PairRateResource;
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

    public function index(string $currency, string $base, ?int $perPage = null, int $page = 1): JsonResponse
    {
        $currency = EnumHelper::caseByName(CurrencyEnum::cases(), $currency, "Invalid currency: $currency");
        $base = EnumHelper::caseByName(CurrencyEnum::cases(), $base, "Invalid currency: $base");
        $rates = $this->currencyRateRepository->getPairRatesByIso($currency, $base, $perPage, $page);
        $pagesCount = $this->currencyRateRepository->getPairRatesPagesCount($currency, $base, $perPage);

        return response()->json([
            'success' => true,
            'data' => PairRateResource::collection($rates),
            'pages' => $pagesCount
        ], Response::HTTP_OK);
    }

    public function latest(string $currency, string $base): JsonResponse
    {
        $currency = EnumHelper::caseByName(CurrencyEnum::cases(), $currency, "Invalid currency: $currency");
        $base = EnumHelper::caseByName(CurrencyEnum::cases(), $base, "Invalid currency: $base");
        $rate = $this->currencyRateRepository->getLatestPairRateByIso($currency, $base);

        return response()->json([
            'success' => true,
            'data' => PairRateResource::make($rate),
        ], Response::HTTP_OK);
    }
}
