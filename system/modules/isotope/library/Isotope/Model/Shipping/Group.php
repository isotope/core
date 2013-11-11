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

use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopeShipping;
use Isotope\Model\Shipping;


class Group extends Shipping implements IsotopeShipping
{

    /**
     * Shipping methods we're using
     * @var array
     */
    protected $arrMethods = array();


    /**
     * Load shipping methods
     * @param   array
     * @return  self
     */
    public function setRow(array $arrData)
    {
        parent::setRow($arrData);

        // Reset existing array
        $this->arrMethods = array();

        if (($objMethods = Shipping::findMultipleByIds(deserialize($this->group_methods, true))) !== null) {
            foreach ($objMethods as $objMethod) {
                if ($objMethod->isAvailable()) {
                    $this->arrMethods[] = $objMethod;
                }
            }
        }

        return $this;
    }

    /**
     * Is available if at least one shipping method was available
     * @return bool
     */
    public function isAvailable()
    {
        return !empty($this->arrMethods);
    }

    /**
     * Return calculated price for this shipping method
     * @return float
     */
    public function getPrice(IsotopeProductCollection $objCollection=null)
    {
        if (empty($this->arrMethods)) {
            return 0;
        }

        switch ($this->group_calculation) {

            default:
            case 'first':
                return $this->arrMethods[0]->getPrice();

            case 'lowest':
                $fltReturn = null;
                foreach ($this->arrMethods as $objMethod) {
                    $fltPrice = $objMethod->getPrice();
                    if (null === $fltReturn || $fltPrice < $fltReturn) {
                        $fltReturn = $fltPrice;
                    }
                }
                return ($fltReturn === null) ? 0 : $fltReturn;

            case 'highest':
                $fltReturn = null;
                foreach ($this->arrMethods as $objMethod) {
                    $fltPrice = $objMethod->getPrice();
                    if (null === $fltReturn || $fltPrice > $fltReturn) {
                        $fltReturn = $fltPrice;
                    }
                }
                return ($fltReturn === null) ? 0 : $fltReturn;

            case 'summarize':
                $fltTotal = 0;
                foreach ($this->arrMethods as $objMethod) {
                    $fltTotal += $objMethod->getPrice();
                }
                return $fltTotal;
        }
    }
}
