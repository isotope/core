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
    protected $arrMethods = false;


    /**
     * Load shipping methods
     * @param   array
     * @return  self
     */
    public function setRow(array $arrData)
    {
        parent::setRow($arrData);

        // Reset existing array
        $this->arrMethods = false;

        return $this;
    }

    /**
     * Is available if at least one shipping method was available
     * @return  bool
     */
    public function isAvailable()
    {
        $this->getGroupMethods();

        return !empty($this->arrMethods);
    }

    /**
     * Return calculated price for this shipping method
     * @return float
     */
    public function getPrice(IsotopeProductCollection $objCollection = null)
    {
        $this->getGroupMethods();

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

    /**
     * Get shipping methods for this group
     * Must be lazy-loaded to prevent recursion
     * @return  array
     */
    protected function getGroupMethods()
    {
        if (false === $this->arrMethods) {
            $this->arrMethods = array();
            $arrMethods = deserialize($this->group_methods, true);

            // Prevent recursion if we should load ourselves
            if (($key = array_search($this->id, $arrMethods)) !== false) {
                unset($arrMethods[$key]);
            }

            if (($objMethods = Shipping::findMultipleByIds($arrMethods)) !== null) {
                foreach ($objMethods as $objMethod) {
                    if ($objMethod->isAvailable()) {
                        $this->arrMethods[] = $objMethod;
                    }
                }
            }
        }

        return $this->arrMethods;
    }
}
