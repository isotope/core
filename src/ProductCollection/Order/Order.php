<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Order;

use Isotope\CoreBundle\Checkout\Payment\PaymentInterface;
use Isotope\CoreBundle\Checkout\Shipping\ShippingInterface;
use Isotope\CoreBundle\Model\Address;
use Isotope\CoreBundle\Model\OrderStatus;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Util\ClosureTrait;

class Order extends AbstractProductCollection
{
    use ClosureTrait;

    private const string COLLECTION_TYPE = 'order';

    private \DateTimeInterface|null $locked = null;

    private string|null $documentNumber = null;

    private OrderStatus|\Closure|null $orderStatus = null;

    private OrderStatus|\Closure|null $transactionStatus = null;

    private OrderStatus|\Closure|null $deliveryStatus = null;

    private Address|\Closure|null $billingAddress = null;

    private Address|\Closure|null $shippingAddress = null;

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

    public function getLocked(): ?\DateTimeInterface
    {
        return $this->locked;
    }

    public function setLocked(?\DateTimeInterface $locked): Order
    {
        $this->locked = $locked;
        return $this;
    }

    public function getDocumentNumber(): ?string
    {
        return $this->documentNumber;
    }

    public function setDocumentNumber(?string $documentNumber): self
    {
        $this->documentNumber = $documentNumber;

        return $this;
    }

    public function getOrderStatus(): OrderStatus|null
    {
        $this->resolveClosure($this->orderStatus);

        return $this->orderStatus;
    }

    public function setOrderStatus(OrderStatus|\Closure|null $status): self
    {
        $this->orderStatus = $status;

        return $this;
    }

    public function getTransactionStatus(): OrderStatus|null
    {
        $this->resolveClosure($this->transactionStatus);

        return $this->transactionStatus;
    }

    public function setTransactionStatus(OrderStatus|\Closure|null $status): self
    {
        $this->transactionStatus = $status;

        return $this;
    }

    public function getDeliveryStatus(): OrderStatus|null
    {
        $this->resolveClosure($this->deliveryStatus);

        return $this->deliveryStatus;
    }

    public function setDeliveryStatus(OrderStatus|\Closure|null $status): self
    {
        $this->deliveryStatus = $status;

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
}
