<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

class PairRateResource extends JsonResource
{
    #[ArrayShape([
        'currency' => "\App\Http\Resources\CurrencyResource",
        'base_currency' => "\App\Http\Resources\CurrencyResource",
        'rate' => "mixed",
        'actual_at' => "mixed"
    ])]
    public function toArray(Request $request): array
    {
        return [
            'currency' => CurrencyResource::make($this->currency),
            'base_currency' => CurrencyResource::make($this->baseCurrency),
            'rate' => $this->value,
            'actual_at' => $this->actual_at
        ];
    }
}
