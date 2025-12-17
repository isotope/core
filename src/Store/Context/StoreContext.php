<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Context;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
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
     * @var array<string, mixed>
     */
    private array $properties = [];

    private \DateTimeImmutable $timestamp;
    private Address|null $defaultBillingAddress = null;
    private Address|null $defaultShippingAddress = null;

    /**
     * @internal Use the StoreContextFactory to create a new instance.
     */
    public function __construct(
        private readonly string $identifier,
        private readonly int $storeId,
        private readonly int $configId,
        private readonly int|null $memberId,
        private readonly \Closure $cartGetter,
        private array|\Closure $memberGroups,
        private readonly string $locale,
        private MoneyFormat|\Closure $moneyFormat,
        private PriceDisplay|\Closure $priceDisplay,
        private Currency|\Closure $currency,
        private readonly float $currencyFactor,
        private AddressConfig|\Closure $billingAddressConfig,
        private AddressConfig|\Closure $shippingAddressConfig,
        private Address|\Closure $ownerAddress,
    ) {
        $this->timestamp = new \DateTimeImmutable();
    }

    public function set(string $key, mixed $value): self
    {
        $this->properties[$key] = $value;

        return $this;
    }

    public function get(string $key): mixed
    {
        return $this->properties[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($this->properties[$key]);
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

    /**
     * @return array<int>
     */
    public function getMemberGroups(): array
    {
        $this->resolveClosure($this->memberGroups);

        return $this->memberGroups;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeImmutable $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
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

    public function getBillingAddressConfig(): AddressConfig
    {
        $this->resolveClosure($this->billingAddressConfig);

        return $this->billingAddressConfig;
    }

    public function getShippingAddressConfig(): AddressConfig
    {
        $this->resolveClosure($this->shippingAddressConfig);

        return $this->shippingAddressConfig;
    }

    /**
     * @return array<string>
     */
    public function getShippingCountries(): array
    {
        $this->resolveClosure($this->shippingAddressConfig);

        return $this->shippingAddressConfig->getCountryCodes();
    }

    public function getOwnerAddress(): Address
    {
        $this->resolveClosure($this->ownerAddress);

        return $this->ownerAddress;
    }

    public function getDefaultBillingAddress(): Address|null
    {
        return $this->defaultBillingAddress;
    }

    public function setDefaultBillingAddress(Address|null $defaultBillingAddress): self
    {
        $this->defaultBillingAddress = $defaultBillingAddress;

        return $this;
    }

    public function getDefaultShippingAddress(): Address|null
    {
        return $this->defaultShippingAddress;
    }

    public function setDefaultShippingAddress(Address|null $defaultShippingAddress): self
    {
        $this->defaultShippingAddress = $defaultShippingAddress;

        return $this;
    }

    public function getCart(): Cart
    {
        $getter = $this->cartGetter;

        return $getter($this);
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
