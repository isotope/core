<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

final class MoneyFormat
{
    /**
     * @internal
     */
    public function __construct(
        public readonly int $decimals = 2,
        public readonly string $decimalSeparator = '.',
        public readonly string $thousandsSeparator = '',
        public readonly int $roundingPrecision = 2,
        public readonly float $roundingIncrement = 0.01,
    ) {
    }

    /**
     * @see https://github.com/shopware/shopware/blob/trunk/src/Core/Checkout/Cart/Price/CashRounding.php
     */
    public function mathRound(float $price): float
    {
        return round($price, $this->roundingPrecision) + 0;
    }

    /**
     * @see https://github.com/shopware/shopware/blob/trunk/src/Core/Checkout/Cart/Price/CashRounding.php
     */
    public function moneyRound(float $price): float
    {
        $rounded = $this->mathRound($price);

        if ($this->roundingPrecision > 2) {
            return $rounded;
        }

        $multiplier = 100 / ($this->roundingIncrement * 100);

        return round($rounded * $multiplier) / $multiplier;
    }

    public function format(float $price): string
    {
        return number_format($price, $this->decimals, $this->decimalSeparator, $this->thousandsSeparator);
    }
}
