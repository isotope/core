<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Cart;

use Isotope\CoreBundle\Checkout\Payment\PaymentInterface;
use Isotope\CoreBundle\Checkout\Shipping\ShippingInterface;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\Model\Address;

class Cart extends AbstractProductCollection
{
    public const string COLLECTION_TYPE = 'cart';

    private Address|\Closure|null $billingAddress = null;
    private Address|\Closure|null $shippingAddress = null;
    private PaymentInterface|\Closure|null $paymentMethod = null;
    private ShippingInterface|\Closure|null $shippingMethod = null;
    private array $subtotals = [];
    private CalculatedPrice|null $total = null;

    public function getBillingAddress(): ?Address
    {
        $this->resolveClosure($this->billingAddress);

        return $this->billingAddress;
    }

    public function setBillingAddress(Address|\Closure|null $billingAddress): Cart
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    public function getShippingAddress(): ?Address
    {
        $this->resolveClosure($this->shippingAddress);

        return $this->shippingAddress;
    }

    public function setShippingAddress(Address|\Closure|null $shippingAddress): Cart
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    public function getPaymentMethod(): ?PaymentInterface
    {
        $this->resolveClosure($this->paymentMethod);

        return $this->paymentMethod;
    }

    public function setPaymentMethod(PaymentInterface|\Closure|null $paymentMethod): Cart
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getShippingMethod(): ?ShippingInterface
    {
        $this->resolveClosure($this->shippingMethod);

        return $this->shippingMethod;
    }

    public function setShippingMethod(ShippingInterface|\Closure|null $shippingMethod): self
    {
        $this->shippingMethod = $shippingMethod;
        return $this;
    }

    public function getSubtotal(int $level = PHP_INT_MAX): float
    {
        $totals = array_filter(
            $this->subtotals,
            static fn (int $key) => $key <= $level,
            ARRAY_FILTER_USE_KEY,
        );

        if ([] === $totals) {
            return 0;
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

    public function getTotal(): ?CalculatedPrice
    {
        return $this->total;
    }

    public function setTotal(?CalculatedPrice $total): self
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
