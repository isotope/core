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

namespace Isotope\CheckoutStep;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopeCheckoutStep;


class ShippingMethod extends CheckoutStep implements IsotopeCheckoutStep
{

    /**
     * Returns true if the current cart has shipping
     * @return  bool
     */
    public function isAvailable()
    {
        return Isotope::getCart()->requiresShipping();
    }

    /**
     * Generate the checkout step
     * @return  string
     */
    public function generate()
    {
        $arrModules = array();
        $arrModuleIds = deserialize($this->iso_shipping_modules);

        if (is_array($arrModuleIds) && !empty($arrModuleIds)) {

            $arrData = \Input::post('shipping');
            $arrModuleIds = array_map('intval', $arrModuleIds);

            $objModules = Shipping::findBy(array('id IN (' . implode(',', $arrModuleIds) . ')', (BE_USER_LOGGED_IN === true ? '' : "enabled='1'")), null, array('order'=>$this->Database->findInSet('id', $arrModuleIds)));

            while ($objModules->next())
            {
                $objModule = $objModules->current();

                if (!$objModule->isAvailable()) {
                    continue;
                }

                if (is_array($arrData) && $arrData['module'] == $objModule->id) {
                    $_SESSION['CHECKOUT_DATA']['shipping'] = $arrData;
                }

                if (is_array($_SESSION['CHECKOUT_DATA']['shipping']) && $_SESSION['CHECKOUT_DATA']['shipping']['module'] == $objModule->id) {
                    Isotope::getCart()->setShippingMethod($objModule);
                }

                $fltPrice = $objModule->price;
                $strSurcharge = $objModule->surcharge;
                $strPrice = $fltPrice != 0 ? (($strSurcharge == '' ? '' : ' ('.$strSurcharge.')') . ': '.Isotope::formatPriceWithCurrency($fltPrice)) : '';

                $arrModules[] = array(
                    'id'        => $objModule->id,
                    'label'     => $objModule->label,
                    'price'     => $strPrice,
                    'checked'   => ((Isotope::getCart()->getShippingMethod()->id == $objModule->id || $objModules->numRows == 1) ? ' checked="checked"' : ''),
                    'note'      => $objModule->note,
                    'form'      => $objModule->getShippingOptions($this),
                );

                $objLastModule = $objModule;
            }
        }

        if (empty($arrModules)) {
            $this->blnError = true;
            $this->Template->showNext = false;

            $objTemplate = new \Isotope\Template('mod_message');
            $objTemplate->class = 'shipping_method';
            $objTemplate->hl = 'h2';
            $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['shipping_method'];
            $objTemplate->type = 'error';
            $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['noShippingModules'];

            return $objTemplate->parse();
        }

        $objTemplate = new \Isotope\Template('iso_checkout_shipping_method');

        if (!Isotope::getCart()->hasShipping() && !strlen($_SESSION['CHECKOUT_DATA']['shipping']['module']) && count($arrModules) == 1) {

            Isotope::getCart()->setShippingMethod($objLastModule);
            $_SESSION['CHECKOUT_DATA']['shipping']['module'] = Isotope::getCart()->getShippingMethod()->id;
            $arrModules[0]['checked'] = ' checked="checked"';

        } elseif (!Isotope::getCart()->hasShipping()) {

            if (\Input::post('FORM_SUBMIT') != '') {
                $objTemplate->error = $GLOBALS['TL_LANG']['MSC']['shipping_method_missing'];
            }

            $this->blnError = true;
        }

        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['shipping_method'];
        $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['shipping_method_message'];
        $objTemplate->shippingMethods = $arrModules;

        if (!$this->hasError()) {
            $objShipping = Isotope::getCart()->getShippingMethod();
            $this->objModule->arrOrderData['shipping_method_id']   = $objShipping->id;
            $this->objModule->arrOrderData['shipping_method']      = $objShipping->label;
            $this->objModule->arrOrderData['shipping_note']        = $objShipping->note;
            $this->objModule->arrOrderData['shipping_note_text']   = strip_tags($objShipping->note);
        }

        // Remove payment step if items are free of charge
        if (!Isotope::getCart()->requiresPayment()) {
            unset($GLOBALS['ISO_CHECKOUT_STEPS']['payment']);
        }

        return $objTemplate->parse();
    }


    public function review()
    {
        return array
        (
            'shipping_method' => array
            (
                'headline'    => $GLOBALS['TL_LANG']['MSC']['shipping_method'],
                'info'        => Isotope::getCart()->getShippingMethod()->checkoutReview(),
                'note'        => Isotope::getCart()->getShippingMethod()->note,
                'edit'        => $this->addToUrl('step=shipping', true),
            ),
        );
    }
}
