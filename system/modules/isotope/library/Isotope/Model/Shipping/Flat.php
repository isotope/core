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
use Isotope\Interfaces\IsotopeShipping;
use Isotope\Isotope;
use Isotope\Model\Shipping;


/**
 * Class ShippingFlat
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Flat extends Shipping implements IsotopeShipping
{

    /**
     * Return calculated price for this shipping method
     * @return float
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

        if ($this->flatCalculation == 'perProduct' || $this->flatCalculation == 'perItem') {
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

            $fltPrice = ($fltPrice * $intMultiplier);
        }

        return Isotope::calculatePrice($fltPrice, $this, 'price', $this->arrData['tax_class']);
    }
}
