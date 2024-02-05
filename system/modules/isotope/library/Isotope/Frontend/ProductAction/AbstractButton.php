<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

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
        $objTemplate = new Template('iso_action');

        $objTemplate->name = $this->getName();
        $objTemplate->classes = $this->getClasses($product);
        $objTemplate->label = $this->getLabel($product);

        return $objTemplate->parse();
    }

    /**
     * Gets the CSS class(es) for this button.
     *
     *
     * @return string
     */
    protected function getClasses(IsotopeProduct $product)
    {
        return '';
    }
}
