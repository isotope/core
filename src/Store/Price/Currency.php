<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

use Symfony\Component\Intl\Currencies;
use Symfony\Component\Intl\Exception\MissingResourceException;

final readonly class Currency
{
    /**
     * @internal
     */
    public function __construct(
        private string $code,
        private bool $useSymbol,
        private bool $space,
        private bool $after,
        private int $minorUnits,
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function useSymbol(): bool
    {
        return $this->useSymbol;
    }

    public function hasSpace(): bool
    {
        return $this->space;
    }

    public function isBefore(): bool
    {
        return !$this->after;
    }

    public function isAfter(): bool
    {
        return $this->after;
    }

    public function getMinorUnits(): int
    {
        return $this->minorUnits;
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
     * Convert amount to minor unit (e.g. 100 EUR = 10000 Euro Cents).
     */
    public function getAmountInMinorUnits(float $amount): int
    {
        return (int) round($amount * 10 ** $this->minorUnits);
    }
}
