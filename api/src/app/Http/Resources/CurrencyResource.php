<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

class CurrencyResource extends JsonResource
{
    #[ArrayShape(['title' => "mixed", 'iso_code' => "mixed"])]
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->name,
            'iso_code' => $this->iso_code
        ];
    }
}
