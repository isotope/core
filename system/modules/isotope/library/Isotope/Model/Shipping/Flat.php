<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Model\Shipping;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopeShipping;
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
    public function getPrice(IsotopeProductCollection $objCollection=null)
    {
        if (null === $objCollection) {
            $objCollection = Isotope::getCart();
        }

        if ($this->isPercentage()) {
            $fltPrice = $objCollection->getSubtotal() / 100 * $this->getPercentage();
        } else {
            $fltPrice = (float) $this->arrData['price'];
        }

        switch ($this->flatCalculation) {
            case 'perProduct':
                $fltPrice = ($fltPrice * $objCollection->countItems());

            case 'perItem':
                $fltPrice = ($fltPrice * $objCollection->sumItemsQuantity());
        }

        return Isotope::calculatePrice($fltPrice, $this, 'price', $this->arrData['tax_class']);
    }
}
