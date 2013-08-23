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

namespace Isotope\Factory;

use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopeShipping;

class ProductCollectionSurcharge
{
    
    /**
     * Create a product surcharge and calculate taxes if necessary
     * @param string
     * @param string
     * @param integer
     * @param array
     * @param object
     */
    public static function buildPaymentSurcharge(IsotopePayment $objPayment, IsotopeProductCollection $objCollection)
    {
        return static::buildSurcharge('Isotope\Model\ProductCollectionSurcharge\Payment', $GLOBALS['TL_LANG']['MSC']['paymentLabel'], $objPayment, $objCollection);
    }
    
    
    public static function buildShippingSurcharge(IsotopeShipping $objShipping, IsotopeProductCollection $objCollection)
    {
        return static::buildSurcharge('Isotope\Model\ProductCollectionSurcharge\Shipping', $GLOBALS['TL_LANG']['MSC']['shippingLabel'], $objShipping, $objCollection);
    }
    
    
    protected static function buildSurcharge($strClass, $strLabel, $objSource, IsotopeProductCollection $objCollection)
    {
        $intTaxClass = $objSource->tax_class;

        $objSurcharge = new $strClass();
        $objSurcharge->label = ($strLabel . ' (' . $objSource->getLabel() . ')');
        $objSurcharge->price = ($objSource->isPercentage() ? $objSource->getPercentage().'%' : '&nbsp;');
        $objSurcharge->total_price = $objSource->getPrice();
        $objSurcharge->tax_class = $intTaxClass;
        $objSurcharge->before_tax = ($intTaxClass ? true : false);

        if ($intTaxClass == -1)
        {
            $objSurcharge->applySplittedTax($objCollection);
        }

        return $objSurcharge;
    }
}
