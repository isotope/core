<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Cart;

use Isotope\CoreBundle\Checkout\Payment\PaymentInterface;
use Isotope\CoreBundle\Checkout\Shipping\ShippingInterface;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Tax\CalculatedTaxCollection;
use Isotope\CoreBundle\Store\Tax\TaxRateCollection;
use Isotope\Model\Address;

class Cart extends AbstractProductCollection
{
    public const string COLLECTION_TYPE = 'cart';

    private bool $isModified = false;

    private Address|\Closure|null $billingAddress = null;

    private Address|\Closure|null $shippingAddress = null;

    private \Closure|PaymentInterface|null $paymentMethod = null;

    private \Closure|ShippingInterface|null $shippingMethod = null;

    private array $subtotals = [];

    private CalculatedPrice|null $total = null;

    public static function getType(): string
    {
        return self::COLLECTION_TYPE;
    }

    public function isModified(): bool
    {
        return $this->isModified;
    }

    public function setModified(bool $isModified = true): Cart
    {
        $this->isModified = $isModified;

        return $this;
    }

    public function getBillingAddress(): Address|null
    {
        $this->resolveClosure($this->billingAddress);

        return $this->billingAddress;
    }

    public function setBillingAddress(Address|\Closure|null $billingAddress): self
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    public function getShippingAddress(): Address|null
    {
        $this->resolveClosure($this->shippingAddress);

        return $this->shippingAddress;
    }

    public function setShippingAddress(Address|\Closure|null $shippingAddress): self
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    public function getPaymentMethod(): PaymentInterface|null
    {
        $this->resolveClosure($this->paymentMethod);

        return $this->paymentMethod;
    }

    public function setPaymentMethod(\Closure|PaymentInterface|null $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getShippingMethod(): ShippingInterface|null
    {
        $this->resolveClosure($this->shippingMethod);

        return $this->shippingMethod;
    }

    public function setShippingMethod(\Closure|ShippingInterface|null $shippingMethod): self
    {
        $this->shippingMethod = $shippingMethod;

        return $this;
    }

    public function getSubtotal(int $level = PHP_INT_MAX): CalculatedPrice
    {
        $totals = array_filter(
            $this->subtotals,
            static fn (int $key) => $key <= $level,
            ARRAY_FILTER_USE_KEY,
        );

        if ([] === $totals) {
            return new CalculatedPrice(0, 0, new CalculatedTaxCollection(), new TaxRateCollection());
        }

        if (\count($totals) < 2) {
            return array_first($totals);
        }

        $key = max(array_keys($totals));

        return $totals[$key];
    }

    public function setSubtotals(array $subtotals): self
    {
        ksort($subtotals);

        $this->subtotals = $subtotals;

        return $this;
    }

    public function getTotal(): CalculatedPrice|null
    {
        return $this->total;
    }

    public function setTotal(CalculatedPrice|null $total): self
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Evaluates closures to retrieve the value.
     */
    protected function resolveClosure(mixed &$property): void
    {
        if ($property instanceof \Closure) {
            $property = $property($this);
        }
    }
}
