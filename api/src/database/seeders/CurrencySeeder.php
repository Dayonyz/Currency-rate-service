<?php

namespace Database\Seeders;

use App\Enums\CurrencyEnum;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (CurrencyEnum::cases() as $currency) {
            Currency::create([
             'name' => $currency->value,
             'iso_code' => $currency->name,
         ]);
        }
    }
}
