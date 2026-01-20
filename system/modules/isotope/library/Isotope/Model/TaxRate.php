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

use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Isotope\CoreBundle\Util\PostalCodeUtil;
use Isotope\Isotope;

/**
 * TaxRate implements the tax rate model.
 *
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property string $name
 * @property string $label
 * @property string $address
 * @property string $countries
 * @property string $subdivisions
 * @property string $postalCodes
 * @property string $rate
 * @property int    $rule
 */
class TaxRate extends Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_tax_rate';


    /**
     * Determine if this tax rate is applicable
     *
     * @param float $fltPrice
     * @param array $arrAddresses
     *
     * @return bool
     */
    public function isApplicable($fltPrice, $arrAddresses)
    {
        // Tax rate is limited to another store config
        if ($this->config > 0 && $this->config != Isotope::getConfig()->id) {
            return false;
        }

        // Tax rate is protected, check member groups
        if ($this->protected && !System::getContainer()->get('security.helper')->isGranted(ContaoCorePermissions::MEMBER_IN_GROUPS, $this->groups)) {
            return false;
        }

        // !HOOK: use tax rate
        if (isset($GLOBALS['ISO_HOOKS']['useTaxRate']) && \is_array($GLOBALS['ISO_HOOKS']['useTaxRate'])) {
            foreach ($GLOBALS['ISO_HOOKS']['useTaxRate'] as $callback) {
                $varValue = System::importStatic($callback[0])->{$callback[1]}($this, $fltPrice, $arrAddresses);

                if ($varValue !== true) {
                    return false;
                }
            }
        }

        $arrAddress = StringUtil::deserialize($this->address);
        if (!empty($arrAddress) && \is_array($arrAddress)) {
            foreach ($arrAddresses as $name => $objAddress) {
                if (!$objAddress instanceof Address || !\in_array($name, $arrAddress, true)) {
                    continue;
                }

                if ($this->countries && !\in_array($objAddress->country, explode(',', $this->countries))) {
                    continue;
                }

                if ($this->subdivisions && !\in_array($objAddress->subdivision, explode(',', $this->subdivisions))) {
                    continue;
                }

                // Check if address has a valid postal code
                if ($this->postalCodes != '') {
                    $arrCodes = PostalCodeUtil::parseRange($this->postalCodes);

                    if (!\in_array($objAddress->postal, $arrCodes)) {
                        continue;
                    }
                }

                $arrPrice = StringUtil::deserialize($this->amount);

                if (\is_array($arrPrice) && !empty($arrPrice) && \strlen($arrPrice[0])) {
                    if (\strlen($arrPrice[1])) {
                        if ($arrPrice[0] > $fltPrice || $arrPrice[1] < $fltPrice) {
                            continue;
                        }
                    } else {
                        if ($arrPrice[0] != $fltPrice) {
                            continue;
                        }
                    }
                }

                // This address is valid, otherwise one of the check would have skipped this (continue)
                return true;
            }

            // No address has passed all checks and returned true
            return false;
        }

        // Addresses are not checked at all, return true
        return true;
    }

    /**
     * Get label
     * @return  string
     */
    public function getLabel()
    {
        return System::getContainer()->get('translator')->trans($this->label ? : $this->name, [], 'isotope');
    }

    /**
     * Get amount of tax rate
     * @return float
     */
    public function getAmount()
    {
        return (float) $this->rate;
    }


    /**
     * Calculate tax amount when included in a price
     * @param  float
     * @return float
     */
    public function calculateAmountIncludedInPrice($fltPrice)
    {
        // Percentual amount. Final price / (1 + (tax / 100)
        return $fltPrice - ($fltPrice / (1 + ($this->getAmount() / 100)));
    }


    /**
     * Calculate tax amount when added to a price
     * @param  float
     * @return float
     */
    public function calculateAmountAddedToPrice($fltPrice)
    {
        // Final price * (1 + (tax / 100)
        return ($fltPrice * (1 + ($this->getAmount() / 100))) - $fltPrice;
    }
}
