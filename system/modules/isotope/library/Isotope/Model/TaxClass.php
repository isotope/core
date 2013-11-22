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
use Isotope\Translation;

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
     * Get label
     * @return  string
     */
    public function getLabel()
    {
        return Translation::get($this->label ?: $this->name);
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
            $arrAddresses = array('billing'=>Isotope::getCart()->getBillingAddress(), 'shipping'=>Isotope::getCart()->getShippingAddress());
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
    public function calculateNetPrice($fltPrice)
    {
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
            $arrAddresses = array('billing'=>Isotope::getCart()->getBillingAddress(), 'shipping'=>Isotope::getCart()->getShippingAddress());
        }

        $objIncludes = $this->getRelated('includes');

        if ($objIncludes !== null && !$objIncludes->isApplicable($fltPrice, $arrAddresses))
        {
            $fltPrice -= $objIncludes->calculateAmountIncludedInPrice($fltPrice);
        }

        $objRates = $this->getRelated('rates');

        if ($objRates !== null)
        {
            $objRates->reset();
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
        }

        return $fltPrice;
    }

    /**
     * Find fallback product type
     * @param   array
     * @return  TaxClass|null
     */
    public static function findFallback(array $arrOptions=array())
    {
        return static::findOneBy('fallback', '1', $arrOptions);
    }


    /**
     * Get all tax classes suitable for DCA options, including a "split amonst products" option
     * @param DataContainer
     * @return array
     */
    public static function getOptionsWithSplit()
    {
        $arrTaxes = array();
        $objTaxes = static::findAll(array('order'=>'name'));

        if (null !== $objTaxes) {
            while ($objTaxes->next()) {
                $arrTaxes[$objTaxes->id] = $objTaxes->name;
            }
        }

        $arrTaxes[-1] = $GLOBALS['TL_LANG']['MSC']['splittedTaxRate'];

        return $arrTaxes;
    }
}
