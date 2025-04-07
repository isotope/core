<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Contao\System;
use Isotope\CoreBundle\Checkout\AbstractCheckoutMethod;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Isotope;
use Isotope\Model\ProductCollectionSurcharge;
use Isotope\Model\ProductCollectionSurcharge\Payment;

abstract class AbstractPayment extends AbstractCheckoutMethod implements PaymentInterface
{
    /**
     * @var array<string>|null
     */
    protected array|null $allowedCountries = null;

    /**
     * @var array<int>|null
     */
    protected array|null $allowedShippings = null;

    public function setAllowedCountries(array|null $countryCodes): static
    {
        $this->allowedCountries = $countryCodes;

        return $this;
    }

    public function setAllowedShippings(array|null $ids): static
    {
        $this->allowedShippings = $ids;

        return $this;
    }

    public function isAvailable(IsotopeOrderableCollection $collection): bool
    {
        if (!parent::isAvailable($collection)) {
            return false;
        }

        if (
            null !== $this->allowedCountries
            && !\in_array($collection->getBillingAddress()?->country, $this->allowedCountries, true)
        ) {
            return false;
        }

        if (
            null !== $this->allowedShippings
            && (
                (!$collection->hasShipping() && !\in_array(-1, $this->allowedShippings, true))
                || (
                    $collection->hasShipping()
                    && !\in_array($collection->getShippingMethod()?->getId(), $this->allowedShippings, true)
                )
            )
        ) {
            return false;
        }

        // !HOOK: modify if payment method is available
        if (isset($GLOBALS['ISO_HOOKS']['paymentAvailable']) && \is_array($GLOBALS['ISO_HOOKS']['paymentAvailable'])) {
            foreach ($GLOBALS['ISO_HOOKS']['paymentAvailable'] as $callback) {
                if (!System::importStatic($callback[0])->{$callback[1]}($this)) {
                    return false;
                }
            }
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
