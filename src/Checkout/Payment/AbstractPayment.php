<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Isotope\CoreBundle\Checkout\AbstractCheckoutMethod;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Isotope;
use Isotope\Model\ProductCollectionSurcharge;
use Isotope\Model\ProductCollectionSurcharge\Payment;

abstract class AbstractPayment extends AbstractCheckoutMethod implements PaymentInterface
{
    /**
     * @var array<int>|null
     */
    protected array|null $allowedShippings = null;

    public function setAllowedShippings(array|null $ids): static
    {
        $this->allowedShippings = $ids;

        return $this;
    }

    public function isAvailable(StoreContext $context): bool
    {
        if (!parent::isAvailable($context)) {
            return false;
        }

        $shipping = $context->getCart()->getShippingMethod();

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

    public function getPrice(IsotopeOrderableCollection $collection): float|null
    {
        $price = $this->calculatePrice($collection);

        if (null === $price) {
            return null;
        }

        return Isotope::calculatePrice($price, $this, 'price', $this->taxClass);
    }

    public function getSurcharge(IsotopeOrderableCollection $collection): Payment|null
    {
        if (null === $this->price) {
            return null;
        }

        return ProductCollectionSurcharge::createForPaymentInCollection($this, $collection);
    }
}
