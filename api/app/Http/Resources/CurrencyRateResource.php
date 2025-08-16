<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

class CurrencyRateResource extends JsonResource
{
    #[ArrayShape([
        'currency' => "array",
        'base_currency' => "array",
        'rate' => "mixed",
        'actual_at' => "mixed"
    ])]
    public function toArray(Request $request): array
    {
        return [
            'currency' => [
                'title' => $this->currency_iso->getTitle(),
                'iso_code' => $this->currency_iso->name
            ],
            'base_currency' => [
                'title' => $this->base_currency_iso->getTitle(),
                'iso_code' => $this->base_currency_iso->name
            ],
            'rate' => $this->value,
            'actual_at' => $this->actual_at
        ];
    }
}
