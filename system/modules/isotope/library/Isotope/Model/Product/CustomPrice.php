<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Product;

use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\ProductPrice;

/**
 * A product that can have a custom price that is entered in the frontend.
 */
class CustomPrice extends Standard
{
    /**
     * @inheritdoc
     */
    public function getPrice(IsotopeProductCollection $objCollection = null)
    {
        if (!isset($this->arrCustomerConfig['customPrice'])) {
            return null;
        }

        $price      = new ProductPrice();
        $price->pid = $this->id;
        $price->setTiers([1 => $this->arrCustomerConfig['customPrice']]);
        $price->preventSaving(false);

        return $price;
    }
}
