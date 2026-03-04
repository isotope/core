<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Contao\Module;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Isotope\Interfaces\IsotopeOrderableCollection;
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
     * Return label for the payment method.
     */
    public function getLabel(): string;

    /**
     * Returns the payment note.
     */
    public function getNote(): string;

    /**
     * Returns whether the payment method is available.
     */
    public function isAvailable(): bool;

    public function hasError(): bool;

    public function getLevel(): int;

    public function getPriceDefinition(): PriceDefinition|null;

    public function review(): TemplateMarkup|null;



    /**
     * Process payment on checkout confirmation page.
     */
    public function processPayment(IsotopeOrderableCollection $order, Module $objModule): bool|string;
}
