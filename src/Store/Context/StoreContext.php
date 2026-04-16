<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Context;

use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\Store\Address\AddressConfig;
use Isotope\CoreBundle\Store\Price\Currency;
use Isotope\CoreBundle\Store\Price\MoneyFormat;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Price\TaxCalculation;
use Isotope\CoreBundle\Util\ClosureTrait;

/**
 * StoreContext holds information about the currently active Isotope shop, including
 * the active member, shop config, money formatting, net or gross prices etc.
 */
final class StoreContext
{
    use ClosureTrait;

    /**
     * @var array<string, mixed>
     */
    private array $properties = [];

    private \DateTimeImmutable $timestamp;

    private AddressModel|null $defaultBillingAddress = null;

    private AddressModel|null $defaultShippingAddress = null;

    /**
     * @var list<int>
     */
    private array $shippingIds = [];

    /**
     * @var list<int>
     */
    private array $paymentIds = [];

    /**
     * @internal use the StoreContextFactory to create a new instance
     */
    public function __construct(
        private readonly string $identifier,
        private readonly int $storeId,
        private int $configId,
        private int|null $memberId,
        private readonly \Closure $cartGetter,
        private \Closure|array $memberGroups,
        private readonly string $locale,
        private \Closure|MoneyFormat $moneyFormat,
        private \Closure|PriceDisplay $priceDisplay,
        private \Closure|TaxCalculation $taxCalculation,
        private \Closure|Currency $currency,
        private readonly float $currencyFactor,
        private AddressConfig|\Closure $billingAddressConfig,
        private AddressConfig|\Closure $shippingAddressConfig,
        private AddressModel|\Closure $ownerAddress,
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

    public function setConfigId(int $configId): self
    {
        $this->configId = $configId;

        return $this;
    }

    public function getMemberId(): int|null
    {
        return $this->memberId;
    }

    public function setMemberId(int|null $memberId): self
    {
        $this->memberId = $memberId;

        return $this;
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

    public function getTaxCalculation(): TaxCalculation
    {
        $this->resolveClosure($this->taxCalculation);

        return $this->taxCalculation;
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

    public function getOwnerAddress(): AddressModel
    {
        $this->resolveClosure($this->ownerAddress);

        return $this->ownerAddress;
    }

    public function getDefaultBillingAddress(): AddressModel|null
    {
        return $this->defaultBillingAddress;
    }

    public function setDefaultBillingAddress(AddressModel|null $defaultBillingAddress): self
    {
        $this->defaultBillingAddress = $defaultBillingAddress;

        return $this;
    }

    public function getDefaultShippingAddress(): AddressModel|null
    {
        return $this->defaultShippingAddress;
    }

    public function setDefaultShippingAddress(AddressModel|null $defaultShippingAddress): self
    {
        $this->defaultShippingAddress = $defaultShippingAddress;

        return $this;
    }

    /**
     * @return list<int>
     */
    public function getShippingIds(): array
    {
        return $this->shippingIds;
    }

    /**
     * @param list<int> $shippingIds
     */
    public function setShippingIds(array $shippingIds): self
    {
        $this->shippingIds = $shippingIds;

        return $this;
    }

    /**
     * @return list<int>
     */
    public function getPaymentIds(): array
    {
        return $this->paymentIds;
    }

    /**
     * @param list<int> $paymentIds
     */
    public function setPaymentIds(array $paymentIds): self
    {
        $this->paymentIds = $paymentIds;

        return $this;
    }

    public function getCart(): Cart
    {
        $getter = $this->cartGetter;

        return $getter($this);
    }
}
