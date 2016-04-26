<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Shipping;

use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Shipping;

/**
 * Class Flat
 *
 * @property string flatCalculation
 */
class Flat extends Shipping
{
    /**
     * @inheritdoc
     */
    public function getPrice(IsotopeProductCollection $objCollection = null)
    {
        if (null === $objCollection) {
            $objCollection = Isotope::getCart();
        }

        if ($this->isPercentage()) {
            $fltPrice = $objCollection->getSubtotal() / 100 * $this->getPercentage();
        } else {
            $fltPrice = (float) $this->arrData['price'];
        }

        if ('perProduct' === $this->flatCalculation || 'perItem' === $this->flatCalculation) {
            $arrItems = $objCollection->getItems();
            $arrProductTypes = deserialize($this->product_types);
            $intMultiplier = 0;

            foreach ($arrItems as $objItem) {
                if (!$objItem->hasProduct()
                    || $objItem->getProduct()->isExemptFromShipping()
                    || ($this->product_types_condition == 'calculation' && !in_array($objItem->getProduct()->type, $arrProductTypes))
                ) {
                    continue;
                }

                $intMultiplier += ($this->flatCalculation == 'perProduct') ? 1 : $objItem->quantity;
            }

            $fltPrice *= $intMultiplier;
        }

        return Isotope::calculatePrice($fltPrice, $this, 'price', $this->arrData['tax_class']);
    }
}
