<?php

namespace App\Dto;

use App\Enums\CurrencyEnum;

class PairRateDto
{
    private CurrencyEnum $currency;
    private CurrencyEnum $baseCurrency;
    private int $precision;
    private int $units;
    private string $dateTime;

    public function __construct(
        CurrencyEnum $currency,
        CurrencyEnum $baseCurrency,
        string       $rate,
        \DateTime    $dateTime
    ){
        $this->setRate($rate);
        $this->setPair($currency, $baseCurrency);
        $this->dateTime = $dateTime->format('Y-m-d H:i:s');
    }

    protected function setPair(CurrencyEnum $currency, CurrencyEnum $base): void
    {
        if ($currency->name === $base->name) {
            throw new \InvalidArgumentException("Pair must must have different currencies, given: $currency->name");
        }
        $this->currency = $currency;
        $this->baseCurrency = $base;
    }

    protected function setRate(string $rate): void
    {
        $intFractional = explode('.', $rate);

        if (count($intFractional) === 1) {
            $this->precision = 0;
            $this->units = (int)$intFractional[0];
            return;
        }

        if (count($intFractional) === 2) {
            $this->precision = strlen($intFractional[1]);
            $this->units = round(pow(10, $this->precision)*$rate, $this->precision);
            return;
        }

        throw new \InvalidArgumentException("Incorrect rate float type: $rate, must be like 99.999 with any digit count");
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function getUnits(): int
    {
        return $this->units;
    }

    public function getCurrency(): CurrencyEnum
    {
        return $this->currency;
    }

    public function getBaseCurrency(): CurrencyEnum
    {
        return $this->baseCurrency;
    }

    /**
     * @throws \Exception
     */
    public function getActualAtDateTime(): \DateTime
    {
        return new \DateTime($this->dateTime);
    }
}