<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Isotope\Helper\Scope;
use Haste\Generator\RowClass;
use Isotope\Isotope;
use Isotope\Model\Shipping;
use Isotope\Template;

/**
 * ShippingCalculator frontend module calculates the shipping price for the current cart.
 *
 * @property mixed $iso_shipping_modules
 */
class ShippingCalculator extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_shipping_calculator';

    /**
     * @inheritDoc
     */
    protected function getSerializedProperties()
    {
        $props = parent::getSerializedProperties();

        $props[] = 'iso_shipping_modules';

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        if (Scope::isBackend()) {
            return $this->generateWildcard();
        }

        if (0 === \count($this->iso_shipping_modules) || (Isotope::getCart()->isEmpty() && !$this->iso_emptyMessage)) {
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

        if ($objCart->isEmpty()) {
            $this->Template          = new Template('mod_message');
            $this->Template->message = $this->iso_noProducts;
            $this->Template->type    = 'empty';

            return;
        }

        $this->Template->showResults      = true;
        $this->Template->requiresShipping = true;
        $this->Template->shippingMethods  = [];

        $objAddress = $objCart->getShippingAddress();

        if (!$objCart->requiresShipping()) {
            $this->Template->requiresShipping = false;
            return;
        }

        // There is no address
        if (!$objAddress->id) {
            $this->Template->showResults = false;
            return;
        }

        /* @var Shipping[] $objShippingMethods */
        $objShippingMethods = Shipping::findMultipleByIds($this->iso_shipping_modules);

        if (null === $objShippingMethods) {
            return;
        }

        $arrMethods = [];

        foreach ($objShippingMethods as $objShipping) {
            if (!$objShipping->isAvailable()) {
                continue;
            }

            $fltPrice = $objShipping->getPrice();

            $arrMethods[] = [
                'label'           => $objShipping->getLabel(),
                'price'           => $fltPrice,
                'formatted_price' => Isotope::formatPriceWithCurrency($fltPrice),
                'shipping'        => $objShipping
            ];
        }

        RowClass::withKey('rowClass')
            ->addCount('row_')
            ->addFirstLast('row_')
            ->addEvenOdd('row_')
            ->applyTo($arrMethods)
        ;

        $this->Template->shippingMethods = $arrMethods;
    }
}
