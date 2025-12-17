<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

use Symfony\Component\Intl\Currencies;
use Symfony\Component\Intl\Exception\MissingResourceException;

final class Currency
{
    /**
     * @internal
     */
    public function __construct(
        private readonly string $code,
        private bool $useSymbol,
        public bool $space,
        public bool $after,
        public int $minorUnits,
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getSymbol(): string|null
    {
        if (!$this->useSymbol) {
            return null;
        }

        try {
            $symbol = Currencies::getSymbol($this->code);

            return $symbol === $this->code ? null : $symbol;
        } catch (MissingResourceException) {
            return null;
        }
    }

    /**
     * Convert amount to minor unit (e.g. 100 EUR = 10000 Euro Cents)
     */
    public function getAmountInMinorUnits(float $amount): int
    {
        return (int) round($amount * 10 ** $this->minorUnits);
    }
}
