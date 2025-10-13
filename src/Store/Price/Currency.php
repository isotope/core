<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

final class Currency
{
    /**
     * @internal
     */
    public function __construct(
        public readonly string $isoCode,
        public readonly string|null $symbol,
        public readonly bool $space,
        public readonly string $position,
        public readonly int|null $isoNumber,
        public readonly int $minorUnits,
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
