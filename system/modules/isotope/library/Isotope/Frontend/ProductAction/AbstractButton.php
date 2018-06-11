<?php

namespace Isotope\Frontend\ProductAction;

use Isotope\Interfaces\IsotopeProduct;
use Isotope\Template;

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
        /** @var Template|\stdClass $objTemplate */
        $objTemplate = new Template('iso_button');

        $objTemplate->name    = $this->getName();
        $objTemplate->classes = $this->getClasses($product);
        $objTemplate->label   = $this->getLabel($product);

        return $objTemplate->parse();
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
