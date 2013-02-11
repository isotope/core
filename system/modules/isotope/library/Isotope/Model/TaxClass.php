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
     * Included tax rate
     * @var TaxRate
     */
    protected $objIncluded = false;

    /**
     * Additionaly tax rates
     * @var array
     */
    protected $arrAdded = false;


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
     * Return the included tax rate
     * @return TaxRate
     */
    public function getIncludedTaxRate()
    {
        if (!$this->includes)
        {
            return null;
        }

        if (false === $this->objIncluded)
        {
            $this->objIncluded = TaxRate::findByPk($this->includes);
        }

        return $this->objIncluded;
    }


    /**
     * Get included tax rates
     * return array
     */
    public function getAddedTaxRates()
    {
        if (false === $this->arrAdded)
        {
            $this->arrAdded = array();

            $arrRates = $this->rates;

            if (is_array($arrRates) && !empty($arrRates))
            {
                $objTaxRates = TaxRate::findBy(array("id IN (" . implode(',', $arrRates) . ")"), array(), array('order'=>"id=" . implode(" DESC, id=", $arrRates) . " DESC"));

                if (null !== $objTaxRates)
                {
                    while ($objTaxRates->next())
                    {
                        $this->arrAdded[$objTaxRates->id] = $objTaxRates->current();
                    }
                }
            }
        }

        return $this->arrAdded;
    }
}
