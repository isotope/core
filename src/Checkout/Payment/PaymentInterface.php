<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Contao\Module;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Model\ProductCollectionSurcharge\Payment;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface for an Isotope payment method.
 */
interface PaymentInterface
{
    /**
     * Returns the ID of this payment method.
     */
    public function getId(): int;

    /**
     * Returns whether the payment method is available.
     */
    public function isAvailable(StoreContext $context): bool;

    /**
     * Return true if payment price is not a fixed amount.
     */
    public function isPercentage(): bool;

    /**
     * Get the percentage amount (if applicable).
     */
    public function getPercentage(): float;

    /**
     * Return label for the payment method.
     */
    public function getLabel(): string;

    /**
     * Returns the payment note.
     */
    public function getNote(): string;

    public function getLevel(): int;

    public function getPriceDefinition(): PriceDefinition|null;

    /**
     * Return the calculated total price for payment.
     */
    public function getPrice(IsotopeOrderableCollection $collection): float|null;

    /**
     * Return percentage label if price is percentage.
     */
    public function getPercentageLabel(): string;

    /**
     * Return the tax class ID for this payment method.
     */
    public function getTaxClass(IsotopeOrderableCollection $collection): int;

    /**
     * Return the checkout review information.
     * Use this to return custom checkout information about this payment module.
     * Example: parial information about the used credit card.
     */
    public function checkoutReview(IsotopeOrderableCollection $collection): string;

    /**
     * Get the checkout surcharge for this shipping method.
     */
    public function getSurcharge(IsotopeOrderableCollection $collection): Payment|null;

    /**
     * Process payment on checkout confirmation page.
     */
    public function processPayment(IsotopeOrderableCollection $order, Module $objModule): bool|string;

    /**
     * Return a html form for checkout or false.
     */
    public function checkoutForm(IsotopeOrderableCollection $order, Module $objModule): Response|string|null;
}
