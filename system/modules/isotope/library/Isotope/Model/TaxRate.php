<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2024 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Contao\FrontendUser;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Isotope\Frontend;
use Isotope\Interfaces\IsotopeVatNoValidator;
use Isotope\Isotope;
use Isotope\Translation;

/**
 * TaxRate implements the tax rate model.
 *
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property string $name
 * @property string $label
 * @property array  $address
 * @property array  $countries
 * @property array  $subdivisions
 * @property string $postalCodes
 * @property array  $rate
 * @property array  $amount
 * @property int    $config
 * @property bool   $exemptOnValidVAT
 * @property bool   $stop
 * @property bool   $guests
 * @property bool   $protected
 * @property array  $groups
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

        // Tax rate is for guests only
        if ($this->guests && FE_USER_LOGGED_IN === true && !$this->protected) {
            return false;
        }

        // Tax rate is protected but no member is logged in
        if ($this->protected && FE_USER_LOGGED_IN !== true && !$this->guests) {
            return false;
        }

        // Tax rate is protected and member logged in, check member groups
        if ($this->protected && FE_USER_LOGGED_IN === true) {
            $groups = StringUtil::deserialize($this->groups);

            if (!\is_array($groups) || empty($groups) || !\count(array_intersect($groups, FrontendUser::getInstance()->groups))) {
                return false;
            }
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
                if (!\in_array($name, $arrAddress)) {
                    continue;
                }

                if ($this->countries != '' && !\in_array($objAddress->country, StringUtil::trimsplit(',', $this->countries ?? ''))) {
                    continue;
                }

                if ($this->subdivisions != '' && !\in_array($objAddress->subdivision, StringUtil::trimsplit(',', $this->subdivisions ?? ''))) {
                    continue;
                }

                // Check if address has a valid postal code
                if ($this->postalCodes != '') {
                    $arrCodes = Frontend::parsePostalCodes($this->postalCodes);

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

                if ($this->exemptOnValidVAT) {
                    $validators = StringUtil::deserialize(Isotope::getConfig()->vatNoValidators);
                    if (!empty($validators) && \is_array($validators)) {
                        foreach ($validators as $type) {

                            /** @var IsotopeVatNoValidator $service */
                            $service = new $GLOBALS['ISO_VAT'][$type]();

                            if ($service->exemptTax($objAddress, $this)) {
                                continue 2;
                            }
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
     * Return true if the tax rate is a percentage (not fixed) amount
     * @return bool
     */
    public function isPercentage()
    {
        $arrTaxRate = StringUtil::deserialize($this->rate, true);

        return ('%' === $arrTaxRate['unit']);
    }

    /**
     * Get label
     * @return  string
     */
    public function getLabel()
    {
        return Translation::get($this->label ? : $this->name);
    }

    /**
     * Get amount of tax rate
     * @return float
     */
    public function getAmount()
    {
        $arrTaxRate = StringUtil::deserialize($this->rate, true);

        return (float) $arrTaxRate['value'];
    }


    /**
     * Calculate tax amount when included in a price
     * @param  float
     * @return float
     */
    public function calculateAmountIncludedInPrice($fltPrice)
    {
        // Percentual amount. Final price / (1 + (tax / 100)
        if ($this->isPercentage()) {
            return $fltPrice - ($fltPrice / (1 + ($this->getAmount() / 100)));
        } // Full amount
        else {
            return $this->getAmount();
        }
    }


    /**
     * Calculate tax amount when added to a price
     * @param  float
     * @return float
     */
    public function calculateAmountAddedToPrice($fltPrice)
    {
        // Final price * (1 + (tax / 100)
        if ($this->isPercentage()) {
            return ($fltPrice * (1 + ($this->getAmount() / 100))) - $fltPrice;
        } // Full amount
        else {
            return $this->getAmount();
        }
    }
}
