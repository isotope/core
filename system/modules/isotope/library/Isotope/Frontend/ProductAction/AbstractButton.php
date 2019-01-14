<?php

namespace Isotope\Frontend\ProductAction;

use Contao\FrontendTemplate;
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
        $objTemplate = new FrontendTemplate('iso_button');
        $objTemplate->name = $this->getName();
        $objTemplate->class = implode(' ', ['submit', $this->getName(), $this->getClasses($product)]);
        $objTemplate->value = $this->getLabel($product);

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
