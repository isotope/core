<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Isotope\Collection\AttributeOption;
use Isotope\CoreBundle\Attribute\Option\OptionCollection;
use Isotope\CoreBundle\Attribute\Option\OptionsSource;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Store\Context\ProductContext;
use Isotope\CoreBundle\Store\Context\StoreContext;

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
