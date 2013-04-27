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
use Isotope\Model\Shipping;
use Isotope\Interfaces\IsotopeProductCollection;


/**
 * Class ShippingFlat
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Flat extends Shipping
{

    /**
     * Return an object property
     *
     * @access public
     * @param string
     * @return mixed
     */
    public function __get($strKey)
    {
        switch( $strKey )
        {
            case 'price':
                return Isotope::getInstance()->calculatePrice($this->getPrice(), $this, 'price', $this->arrData['tax_class']);
                break;
        }

        return parent::__get($strKey);
    }


    /**
     * Get the checkout surcharge for this shipping method
     */
    public function getSurcharge($objCollection)
    {
        $fltPrice = $this->getPrice();

        if ($fltPrice == 0)
        {
            return false;
        }

        return Isotope::getInstance()->calculateSurcharge(
                                $fltPrice,
                                ($GLOBALS['TL_LANG']['MSC']['shippingLabel'] . ' (' . $this->label . ')'),
                                $this->arrData['tax_class'],
                                $objCollection->getProducts(),
                                $this);
    }


    /**
     * Calculate the price based on module configuration
     * @return float
     */
    public function getPrice(IsotopeProductCollection $objCollection=null)
    {
        if (null === $objCollection) {
            $objCollection = Isotope::getCart();
        }

        $strPrice = $this->arrData['price'];
        $blnPercentage = substr($strPrice, -1) == '%' ? true : false;

        if ($blnPercentage)
        {
            $fltSurcharge = (float) substr($strPrice, 0, -1);
            $fltPrice = $objCollection->getSubtotal() / 100 * $fltSurcharge;
        }
        else
        {
            $fltPrice = (float) $strPrice;
        }

        switch( $this->flatCalculation )
        {
            case 'perProduct':
                return (($fltPrice * $objCollection->countItems()) + $this->calculateSurcharge($objCollection));

            case 'perItem':
                return (($fltPrice * $objCollection->sumItemsQuantity()) + $this->calculateSurcharge($objCollection));

            default:
                return ($fltPrice + $this->calculateSurcharge($objCollection));
        }
    }


}
