<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Contao\Model;
use Contao\System;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\StoreManager;

/**
 * TaxRate implements the tax class model.
 *
 * @property int    $id
 * @property int    $tstamp
 * @property string $name
 * @property bool   $fallback
 * @property int    $includes
 * @property string $label
 * @property array  $rates
 * @property bool   $applyRoundingIncrement
 * @property bool   $notNegative
 */
class TaxClass extends Model
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
        return System::getContainer()->get('translator')->trans($this->name, [], 'isotope');
    }

    /**
     * Get label
     *
     * @return  string
     */
    public function getLabel()
    {
        return $this->label ? System::getContainer()->get('translator')->trans($this->label, [], 'isotope') : '';
    }

    /**
     * Calculate a price, removing tax if included but not applicable
     *
     * @param float $fltPrice
     *
     * @return float
     */
    public function calculatePrice($fltPrice, array $arrAddresses = null)
    {
        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);

        return match ($storeManager->getCurrentContext()->getPriceDisplay()) {
            PriceDisplay::Net => $this->calculateNetPrice($fltPrice, $arrAddresses),
            PriceDisplay::Gross => $this->calculateGrossPrice($fltPrice, $arrAddresses),
            PriceDisplay::Fixed => $fltPrice,
            default => throw new \RuntimeException('Invalid PriceDisplay config'),
        };
    }


    /**
     * Calculate a price, remove tax if included
     *
     * @param float $fltPrice
     *
     * @return float
     */
    public function calculateNetPrice($fltPrice, array $arrAddresses = null)
    {
        if (!\is_array($arrAddresses)) {
            $arrAddresses = array(
                'billing'  => Isotope::getCart()->getBillingAddress(),
                'shipping' => Isotope::getCart()->getShippingAddress(),
            );
        }

        $fltNetPrice = $fltPrice;

        /** @var \Isotope\Model\TaxRate $objIncludes */
        if (($objIncludes = $this->getRelated('includes')) !== null) {
            $fltNetPrice -= $objIncludes->calculateAmountIncludedInPrice($fltPrice);
        }

        if (Config::PRICE_DISPLAY_FIXED === Isotope::getConfig()->getPriceDisplay()) {
            if (($objRates = $this->getRelated('rates')) !== null) {

                /** @var \Isotope\Model\TaxRate $objTaxRate */
                foreach ($objRates as $objTaxRate) {
                    if ($objTaxRate->isApplicable($fltPrice, $arrAddresses)) {
                        $fltNetPrice -= $objTaxRate->calculateAmountIncludedInPrice($fltPrice);

                        if ($objTaxRate->stop) {
                            break;
                        }
                    }
                }
            }
        }

        return $fltNetPrice;
    }


    /**
     * Calculate a price, add all applicable taxes
     *
     * @param float $fltPrice
     *
     * @return float
     */
    public function calculateGrossPrice($fltPrice, array $arrAddresses = null)
    {
        if (!\is_array($arrAddresses)) {
            $arrAddresses = array(
                'billing'  => Isotope::getCart()->getBillingAddress(),
                'shipping' => Isotope::getCart()->getShippingAddress(),
            );
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
