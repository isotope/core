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

namespace Isotope\Module;

use Haste\Generator\RowClass;
use Isotope\Isotope;
use Isotope\Model\Shipping;


class ShippingCalculator extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_shipping_calculator';

    /**
     * Shipping methods
     * @var array
     */
    protected $arrShippingMethods = array();

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: SHIPPING CALCULATOR ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id    = $this->id;
            $objTemplate->link  = $this->name;
            $objTemplate->href  = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->arrShippingMethods = deserialize($this->iso_shipping_modules, true);

        if (empty($this->arrShippingMethods)) {
            return '';
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        $objCart = Isotope::getCart();
        $objAddress = $objCart->getShippingAddress();
        $this->Template->showResults = false;
        $this->Template->requiresShipping = false;

        // There is no address
        if (!$objAddress->id) {
            return;
        }

        $this->Template->showResults = true;
        $arrMethods = array();

        // Get the shipping methods
        if ($objAddress->id && $objCart->requiresShipping()) {
            $this->Template->requiresShipping = true;
            $objShippingMethods = Shipping::findMultipleByIds($this->arrShippingMethods);

            /* @var Shipping $objShipping */
            foreach ($objShippingMethods as $objShipping) {
                if ($objShipping->isAvailable()) {

                    $fltPrice = $objShipping->getPrice();

                    $arrMethods[] = array(
                        'label' => $objShipping->getLabel(),
                        'price' => $fltPrice,
                        'formatted_price' => Isotope::formatPriceWithCurrency($fltPrice),
                        'shipping' => $objShipping
                    );
                }
            }

            RowClass::withKey('rowClass')->addCount('row_')->addFirstLast('row_')->addEvenOdd('row_')->applyTo(
                $arrMethods
            );
        }

        $this->Template->shippingMethods = $arrMethods;
    }
}
