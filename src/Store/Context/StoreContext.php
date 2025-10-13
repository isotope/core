<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Context;

use Isotope\CoreBundle\Store\Price\Currency;
use Isotope\CoreBundle\Store\Price\MoneyFormat;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\Model\Address;

/**
 * StoreContext holds information about the currently active Isotope shop, including
 * the active member, shop config, money formatting, net or gross prices etc.
 */
final class StoreContext
{
    /**
     * @internal Use the StoreContextFactory to create a new instance.
     */
    public function __construct(
        private readonly string $identifier,
        private readonly int $storeId,
        private readonly int $configId,
        private readonly int|null $memberId,
        private readonly string $locale,
        private MoneyFormat|\Closure $moneyFormat,
        private PriceDisplay|\Closure $priceDisplay,
        private Currency|\Closure $currency,
        private readonly float $currencyFactor,
        private Address|\Closure $ownerAddress,
        private array|\Closure $billingCountries,
        private array|\Closure $shippingCountries,
    ) {
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getStoreId(): int
    {
        return $this->storeId;
    }

    public function getConfigId(): int
    {
        return $this->configId;
    }

    public function getMemberId(): ?int
    {
        return $this->memberId;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getMoneyFormat(): MoneyFormat
    {
        $this->resolveClosure($this->moneyFormat);

        return $this->moneyFormat;
    }

    public function getPriceDisplay(): PriceDisplay
    {
        $this->resolveClosure($this->priceDisplay);

        return $this->priceDisplay;
    }

    public function getCurrency(): Currency
    {
        $this->resolveClosure($this->currency);

        return $this->currency;
    }

    public function getCurrencyFactor(): float
    {
        return $this->currencyFactor;
    }

    public function getOwnerAddress(): Address
    {
        $this->resolveClosure($this->ownerAddress);

        return $this->ownerAddress;
    }

    /**
     * @return array<string>
     */
    public function getBillingCountries(): array
    {
        $this->resolveClosure($this->billingCountries);

        return $this->billingCountries;
    }

    /**
     * @return array<string>
     */
    public function getShippingCountries(): array
    {
        $this->resolveClosure($this->shippingCountries);

        return $this->shippingCountries;
    }

    /**
     * Evaluates closures to retrieve the value.
     */
    private function resolveClosure(mixed &$property): void
    {
        if ($property instanceof \Closure) {
            $property = $property($this);
        }
    }
}
