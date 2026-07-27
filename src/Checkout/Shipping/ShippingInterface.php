<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Isotope\CoreBundle\Twig\TemplateMarkup;

/**
 * Interface for an Isotope shipping method.
 */
interface ShippingInterface
{
    /**
     * Returns the ID of this shipping method.
     */
    public function getId(): int;

    /**
     * Return label for the shipping method.
     */
    public function getLabel(): string;

    /**
     * Returns the shipping note.
     */
    public function getNote(): string;

    /**
     * Return boolean flag if the shipping method is available.
     */
    public function isAvailable(): bool;

    public function hasError(): bool;

    public function getLevel(): int;

    public function getPriceDefinition(): PriceDefinition|null;

    public function review(): TemplateMarkup|null;

    public function getTrackingUrl(): string|null;
}
