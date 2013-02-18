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

namespace Isotope\Model;

use Isotope\Isotope;

/**
 * TaxRate implements the tax class model.
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class TaxClass extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_tax_class';


    /**
     * Get a property, unserialize appropriate fields
     * @param  string
     * @return mixed
     */
    public function __get($strKey)
    {
        switch ($strKey)
        {
            case 'rates':
                return deserialize($this->arrData[$strKey]);

            case 'label':
                return $this->arrData['label'] ? Isotope::getInstance()->translate($this->arrData['label']) : '';

            default:
                return parent::__get($strKey);
        }
    }


    /**
     * Calculate a price, removing tax if included but not applicable
     * @param  float
     * @param  array|null
     * @return float
     */
    public function calculatePrice($fltPrice, $arrAddresses=null)
    {
        if (!is_array($arrAddresses))
        {
            $arrAddresses = array('billing'=>Isotope::getInstance()->Cart->billing_address, 'shipping'=>Isotope::getInstance()->Cart->shipping_address);
        }

        $objIncludes = $this->getRelated('includes');

        if ($objIncludes->id > 0 && !$objIncludes->isApplicable($fltPrice, $arrAddresses))
        {
            $fltPrice -= $objIncludes->calculateAmountIncludedInPrice($fltPrice);
        }

        return $fltPrice;
    }


    /**
     * Calculate a price, remove tax if included
     * @param  float
     * @param  array|null
     * @return float
     */
    public function calculateNetPrice($fltPrice, $arrAddresses=null)
    {
        if (!is_array($arrAddresses))
        {
            $arrAddresses = array('billing'=>Isotope::getInstance()->Cart->billing_address, 'shipping'=>Isotope::getInstance()->Cart->shipping_address);
        }

        $objIncludes = $this->getRelated('includes');

        if ($objIncludes->id > 0)
        {
            $fltPrice -= $objIncludes->calculateAmountIncludedInPrice($fltPrice);
        }

        return $fltPrice;
    }


    /**
     * Calculate a price, add all applicable taxes
     * @param  float
     * @param  array|null
     * @return float
     */
    public function calculateGrossPrice($fltPrice, $arrAddresses=null)
    {
        if (!is_array($arrAddresses))
        {
            $arrAddresses = array('billing'=>Isotope::getInstance()->Cart->billing_address, 'shipping'=>Isotope::getInstance()->Cart->shipping_address);
        }

        $objIncludes = $this->getRelated('includes');

        if ($objIncludes->id > 0 && !$objIncludes->isApplicable($fltPrice, $arrAddresses))
        {
            $fltPrice -= $objIncludes->calculateAmountIncludedInPrice($fltPrice);
        }

        $objRates = $this->getRelated('rates');

        while ($objRates->next())
        {
            $objTaxRate = $objRates->current();

            if ($objTaxRate->isApplicable($fltPrice, $arrAddresses))
            {
                $fltPrice += $objTaxRate->calculateAmountAddedToPrice($fltPrice);

                if ($objTaxRate->stop)
                {
                    break;
                }
            }
        }

        return $fltPrice;
    }
}
