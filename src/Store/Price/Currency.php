<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

final class Currency
{
    /**
     * @internal
     */
    public function __construct(
        public readonly string $code,
        public bool $symbol,
        public bool $space,
        public string $position,
        public int $minorUnits,
    ) {
    }

    /**
     * Convert amount to minor unit (e.g. 100 EUR = 10000 Euro Cents)
     */
    public function getAmountInMinorUnits(float $amount): int
    {
        return (int) round($amount * 10 ** $this->minorUnits);
    }
}
