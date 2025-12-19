<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Isotope\CoreBundle\Attribute\Option\OptionCollection;
use Isotope\CoreBundle\Attribute\Option\OptionsSource;
use Isotope\CoreBundle\Store\Context\ProductContext;

interface OptionsAttributeInterface extends AttributeInterface
{
    /**
     * Returns the options source.
     */
    public function getOptionsSource(): OptionsSource|null;

    public function getOptions(ProductContext|null $productContext = null): OptionCollection;

    /**
     * Return true if attribute can have prices.
     */
    public function canHavePrices(): bool;
}
