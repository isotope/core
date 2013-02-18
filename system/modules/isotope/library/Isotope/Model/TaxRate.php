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
 * TaxRate implements the tax rate model.
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class TaxRate extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_tax_rate';


    /**
     * Get a property, unserialize appropriate fields
     * @param  string
     * @return mixed
     */
    public function __get($strKey)
    {
        switch ($strKey)
        {
            case 'rate':
            case 'address':
                return deserialize($this->arrData[$strKey]);

            case 'label':
                return Isotope::getInstance()->translate($this->arrData['label'] ?: $this->arrData['name']);

            default:
                return parent::__get($strKey);
        }
    }


    /**
     * Determine if this tax rate is applicable
     * @paran  float
     * @param  array
     * @return bool
     */
    public function isApplicable($fltPrice, $arrAddresses)
    {
        // Tax rate is limited to another store config
        if ($this->config > 0 && $this->config != Isotope::getInstance()->Config->id)
        {
            return false;
        }

        // Tax rate is for guests only
        if ($this->guests && FE_USER_LOGGED_IN === true && !$this->protected)
        {
            return false;
        }

        // Tax rate is protected but no member is logged in
        elseif ($this->protected && FE_USER_LOGGED_IN !== true && !$this->guests)
        {
            return false;
        }

        // Tax rate is protected and member logged in, check member groups
        elseif ($this->protected && FE_USER_LOGGED_IN === true)
        {
            $groups = deserialize($this->groups);

            if (!is_array($groups) || empty($groups) || !count(array_intersect($groups, \FrontendUser::getInstance()->groups)))
            {
                return false;
            }
        }

        // !HOOK: use tax rate
        if (isset($GLOBALS['ISO_HOOKS']['useTaxRate']) && is_array($GLOBALS['ISO_HOOKS']['useTaxRate']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['useTaxRate'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $varValue = $objCallback->$callback[1]($this, $fltPrice, $arrAddresses);

                if ($varValue !== true)
                {
                    return false;
                }
            }
        }

        if (is_array($this->address) && count($this->address)) // Can't use empty() because its an object property (using __get)
        {
            foreach ($arrAddresses as $name => $arrAddress)
            {
                if (!in_array($name, $this->address))
                {
                    continue;
                }

                if ($this->countries != '' && !in_array($arrAddress['country'], trimsplit(',', $this->countries)))
                {
                    continue;
                }

                if ($this->subdivisions != '' && !in_array($arrAddress['subdivision'], trimsplit(',', $this->subdivisions)))
                {
                    continue;
                }

                // Check if address has a valid postal code
                if ($this->postalCodes != '')
                {
                    $arrCodes = \Isotope\Frontend::parsePostalCodes($this->postalCodes);

                    if (!in_array($arrAddress['postal'], $arrCodes))
                    {
                        continue;
                    }
                }

                $arrPrice = deserialize($this->amount);

                if (is_array($arrPrice) && !empty($arrPrice) && strlen($arrPrice[0]))
                {
                    if (strlen($arrPrice[1]))
                    {
                        if ($arrPrice[0] > $fltPrice || $arrPrice[1] < $fltPrice)
                        {
                            continue;
                        }
                    }
                    else
                    {
                        if ($arrPrice[0] != $fltPrice)
                        {
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
     * Return true if the tax rate is a percentage (not fixed) amount
     * @return bool
     */
    public function isPercentage()
    {
        $arrTaxRate = $this->rate;

        return ($arrTaxRate['unit'] == '%');
    }


    /**
     * Get amount of tax rate
     * @return float
     */
    public function getAmount()
    {
        $arrTaxRate = $this->rate;

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
        if ($this->isPercentage())
        {
            return $fltPrice - ($fltPrice / (1 + ($this->getAmount() / 100)));
        }

        // Full amount
        else
        {
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
        if ($this->isPercentage())
        {
            return ($fltPrice * (1 + ($this->getAmount() / 100))) - $fltPrice;
        }

        // Full amount
        else
        {
            return $this->getAmount();
        }
    }
}
