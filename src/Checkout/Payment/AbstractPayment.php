<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Isotope\CoreBundle\Checkout\AbstractCheckoutMethod;
use Isotope\CoreBundle\Store\OrderManager;

abstract class AbstractPayment extends AbstractCheckoutMethod implements PaymentInterface
{
    /**
     * @var array<int>|null
     */
    protected array|null $allowedShippings = null;

    public static function getSubscribedServices(): array
    {
        return [
            ...parent::getSubscribedServices(),
            OrderManager::class,
        ];
    }

    public function setAllowedShippings(array|null $ids): static
    {
        $this->allowedShippings = $ids;

        return $this;
    }

    public function isAvailable(): bool
    {
        if (!parent::isAvailable()) {
            return false;
        }

        $shipping = $this->context->getCart()?->getShippingMethod();

        if (
            null !== $this->allowedShippings
            && (
                (null === $shipping && !\in_array(-1, $this->allowedShippings, true))
                || (
                    $shipping
                    && !\in_array($shipping->getId(), $this->allowedShippings, true)
                )
            )
        ) {
            return false;
        }

        return true;
    }

    protected function updateTransactionStatus(int $orderId, string $status): void
    {
        $this->get(OrderManager::class)->updateOrder($orderId, [
            'transaction_status' => $status,
        ]);
    }
}
