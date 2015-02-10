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

namespace Isotope\Model;

use Isotope\Isotope;
use Isotope\Translation;

/**
 * TaxRate implements the tax class model.
 *
 * @property int    id
 * @property int    tstamp
 * @property string name
 * @property bool   fallback
 * @property int    includes
 * @property string label
 * @property array  rates
 * @property bool   applyRoundingIncrement
 * @property bool   notNegative
 */
class TaxClass extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_tax_class';

    /**
     * Get translated name
     *
     * @return string
     */
    public function getName()
    {
        return Translation::get($this->name);
    }

    /**
     * Get label
     *
     * @return  string
     */
    public function getLabel()
    {
        return $this->label ? Translation::get($this->label) : '';
    }

    /**
     * Calculate a price, removing tax if included but not applicable
     *
     * @param float $fltPrice
     * @param array $arrAddresses
     *
     * @return float
     */
    public function calculatePrice($fltPrice, array $arrAddresses = null)
    {
        if (!is_array($arrAddresses)) {
            $arrAddresses = array('billing' => Isotope::getCart()->getBillingAddress());

            if (Isotope::getCart()->requiresShipping()) {
                $arrAddresses['shipping'] = Isotope::getCart()->getShippingAddress();
            }
        }

        /** @var \Isotope\Model\TaxRate $objIncludes */
        if (($objIncludes = $this->getRelated('includes')) !== null && !$objIncludes->isApplicable($fltPrice, $arrAddresses)) {
            $fltPrice -= $objIncludes->calculateAmountIncludedInPrice($fltPrice);
        }

        return $fltPrice;
    }


    /**
     * Calculate a price, remove tax if included
     *
     * @param float $fltPrice
     *
     * @return float
     */
    public function calculateNetPrice($fltPrice)
    {
        /** @var \Isotope\Model\TaxRate $objIncludes */
        if (($objIncludes = $this->getRelated('includes')) !== null) {
            $fltPrice -= $objIncludes->calculateAmountIncludedInPrice($fltPrice);
        }

        return $fltPrice;
    }


    /**
     * Calculate a price, add all applicable taxes
     *
     * @param float $fltPrice
     * @param array $arrAddresses
     *
     * @return float
     */
    public function calculateGrossPrice($fltPrice, array $arrAddresses = null)
    {
        if (!is_array($arrAddresses)) {
            $arrAddresses = array('billing' => Isotope::getCart()->getBillingAddress());

            if (Isotope::getCart()->requiresShipping()) {
                $arrAddresses['shipping'] = Isotope::getCart()->getShippingAddress();
            }
        }

        /** @var \Isotope\Model\TaxRate $objIncludes */
        if (($objIncludes = $this->getRelated('includes')) !== null && !$objIncludes->isApplicable($fltPrice, $arrAddresses)) {
            $fltPrice -= $objIncludes->calculateAmountIncludedInPrice($fltPrice);
        }

        if (($objRates = $this->getRelated('rates')) !== null) {

            /** @var \Isotope\Model\TaxRate $objTaxRate */
            foreach ($objRates as $objTaxRate) {
                if ($objTaxRate->isApplicable($fltPrice, $arrAddresses)) {
                    $fltPrice += $objTaxRate->calculateAmountAddedToPrice($fltPrice);

                    if ($objTaxRate->stop) {
                        break;
                    }
                }
            }
        }

        return $fltPrice;
    }

    /**
     * Find fallback product type
     *
     * @param array $arrOptions
     *
     * @return TaxClass|null
     */
    public static function findFallback(array $arrOptions = array())
    {
        return static::findOneBy('fallback', '1', $arrOptions);
    }


    /**
     * Get all tax classes suitable for DCA options, including a "split amonst products" option
     *
     * @return array
     */
    public static function getOptionsWithSplit()
    {
        $arrTaxes = array();
        $objTaxes = static::findAll(array('order' => 'name'));

        if (null !== $objTaxes) {
            while ($objTaxes->next()) {
                $arrTaxes[$objTaxes->id] = $objTaxes->name;
            }
        }

        $arrTaxes[-1] = $GLOBALS['TL_LANG']['MSC']['splittedTaxRate'];

        return $arrTaxes;
    }
}
