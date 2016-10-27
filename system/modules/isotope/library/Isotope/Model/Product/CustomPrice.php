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
use Isotope\Model\CustomProductPrice;

/**
 * A product that can have a custom price that is entered in the frontend.
 */
class CustomPrice extends Standard
{
    /**
     * Price attribute name
     * @var string
     */
    protected $priceAttribute = 'customPrice';

    /**
     * Get product price model
     *
     * @param IsotopeProductCollection $objCollection
     *
     * @return \Isotope\Interfaces\IsotopePrice
     */
    public function getPrice(IsotopeProductCollection $objCollection = null)
    {
        $options = $this->getOptions();

        $price      = new CustomProductPrice();
        $price->pid = $this->id;
        $price->setPrice($options[$this->priceAttribute]);
        $price->preventSaving(false);

        return $price;
    }
}
