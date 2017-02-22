<?php

namespace Isotope\Frontend\ProductAction;

use Isotope\Interfaces\IsotopeProduct;

abstract class AbstractButton implements ProductActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function isAvailable(IsotopeProduct $product, array $config = [])
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(IsotopeProduct $product, array $config = [])
    {
        return sprintf(
            '<input type="submit" name="%s" class="submit %s %s" value="%s">',
            $this->getName(),
            $this->getName(),
            $this->getClasses($product),
            $this->getLabel($product)
        );
    }

    /**
     * Gets the CSS class(es) for this button.
     *
     * @param IsotopeProduct $product
     *
     * @return string
     */
    protected function getClasses(IsotopeProduct $product)
    {
        return '';
    }
}
