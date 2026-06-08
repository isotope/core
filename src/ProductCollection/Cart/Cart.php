<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Cart;

use Isotope\CoreBundle\Checkout\Payment\PaymentInterface;
use Isotope\CoreBundle\Checkout\Shipping\ShippingInterface;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\Util\ClosureTrait;

class Cart extends AbstractProductCollection
{
    use ClosureTrait;

    private const string COLLECTION_TYPE = 'cart';

    private bool $isModified = false;

    private AddressModel|\Closure|null $billingAddress = null;

    private AddressModel|\Closure|null $shippingAddress = null;

    private \Closure|PaymentInterface|null $paymentMethod = null;

    private \Closure|ShippingInterface|null $shippingMethod = null;

    public static function getType(): string
    {
        return self::COLLECTION_TYPE;
    }

    public function isModified(): bool
    {
        if ($this->isModified) {
            return true;
        }

        foreach ($this->getExtensions() as $extension) {
            if ($extension->isModified()) {
                return true;
            }
        }

        return false;
    }

    public function setModified(bool $isModified = true): Cart
    {
        $this->isModified = $isModified;

        return $this;
    }

    public function getBillingAddress(): AddressModel|null
    {
        $this->resolveClosure($this->billingAddress);

        return $this->billingAddress;
    }

    public function setBillingAddress(AddressModel|\Closure|null $billingAddress): self
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    public function getShippingAddress(): AddressModel|null
    {
        $this->resolveClosure($this->shippingAddress);

        return $this->shippingAddress;
    }

    public function setShippingAddress(AddressModel|\Closure|null $shippingAddress): self
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
}
