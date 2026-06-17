<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Order;

use Isotope\CoreBundle\Checkout\Payment\PaymentInterface;
use Isotope\CoreBundle\Checkout\Shipping\ShippingInterface;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Model\OrderStatusModel;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Util\ClosureTrait;

class Order extends AbstractProductCollection
{
    use ClosureTrait;

    private const string COLLECTION_TYPE = 'order';

    private \DateTimeInterface|null $locked = null;

    private string|null $documentNumber = null;

    private \Closure|OrderStatusModel|null $orderStatus = null;

    private \Closure|OrderStatusModel|null $transactionStatus = null;

    private \Closure|OrderStatusModel|null $deliveryStatus = null;

    private AddressModel|\Closure|null $billingAddress = null;

    private AddressModel|\Closure|null $shippingAddress = null;

    private \Closure|PaymentInterface|null $paymentMethod = null;

    private \Closure|ShippingInterface|null $shippingMethod = null;

    public function __construct(
        string $identifier,
        private readonly StoreContext $context,
    ) {
        parent::__construct($identifier);
    }

    public function getContext(): StoreContext
    {
        return $this->context;
    }

    public static function getType(): string
    {
        return self::COLLECTION_TYPE;
    }

    public function getLocked(): \DateTimeInterface|null
    {
        return $this->locked;
    }

    public function setLocked(\DateTimeInterface|null $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    public function getDocumentNumber(): string|null
    {
        return $this->documentNumber;
    }

    public function setDocumentNumber(string|null $documentNumber): self
    {
        $this->documentNumber = $documentNumber;

        return $this;
    }

    public function getOrderStatus(): OrderStatusModel|null
    {
        $this->resolveClosure($this->orderStatus);

        return $this->orderStatus;
    }

    public function setOrderStatus(\Closure|OrderStatusModel|null $status): self
    {
        $this->orderStatus = $status;

        return $this;
    }

    public function getTransactionStatus(): OrderStatusModel|null
    {
        $this->resolveClosure($this->transactionStatus);

        return $this->transactionStatus;
    }

    public function setTransactionStatus(\Closure|OrderStatusModel|null $status): self
    {
        $this->transactionStatus = $status;

        return $this;
    }

    public function getDeliveryStatus(): OrderStatusModel|null
    {
        $this->resolveClosure($this->deliveryStatus);

        return $this->deliveryStatus;
    }

    public function setDeliveryStatus(\Closure|OrderStatusModel|null $status): self
    {
        $this->deliveryStatus = $status;

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
