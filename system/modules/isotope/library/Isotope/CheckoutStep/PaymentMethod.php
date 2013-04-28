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
use Isotope\Model\Payment;


class PaymentMethod extends CheckoutStep implements IsotopeCheckoutStep
{

    /**
     * Returns true if the current cart has payment
     * @return  bool
     */
    public function isAvailable()
    {
        return Isotope::getCart()->requiresPayment();
    }

    /**
     * Generate the checkout step
     * @return  string
     */
    public function generate()
    {
        $arrModules = array();
        $arrModuleIds = deserialize($this->objModule->iso_payment_modules);

        if (!empty($arrModuleIds) && is_array($arrModuleIds)) {

            $arrData = \Input::post('payment');
            $arrModuleIds = array_map('intval', $arrModuleIds);

            $objModules = Payment::findBy(array('id IN (' . implode(',', $arrModuleIds) . ')', (BE_USER_LOGGED_IN === true ? '' : "enabled='1'")), null, array('order'=>\Database::getInstance()->findInSet('id', $arrModuleIds)));

            if (null !== $objModules) {
            while ($objModules->next()) {

                $objModule = $objModules->current();

                if (!$objModule->isAvailable()) {
                    continue;
                }

                if (is_array($arrData) && $arrData['module'] == $objModule->id) {
                    $_SESSION['CHECKOUT_DATA']['payment'] = $arrData;
                }

                if (is_array($_SESSION['CHECKOUT_DATA']['payment']) && $_SESSION['CHECKOUT_DATA']['payment']['module'] == $objModule->id) {
                    Isotope::getCart()->Payment = $objModule;
                }

                $fltPrice = $objModule->price;
                $strSurcharge = $objModule->surcharge;
                $strPrice = ($fltPrice != 0) ? (($strSurcharge == '' ? '' : ' ('.$strSurcharge.')') . ': '.Isotope::formatPriceWithCurrency($fltPrice)) : '';

                $arrModules[] = array(
                    'id'        => $objModule->id,
                    'label'     => $objModule->label,
                    'price'     => $strPrice,
                    'checked'   => ((Isotope::getCart()->Payment->id == $objModule->id || $objModules->numRows == 1) ? ' checked="checked"' : ''),
                    'note'      => $objModule->note,
                    'form'      => $objModule->paymentForm($this),
                );

                $objLastModule = $objModule;
            }
        }
        }

        if (empty($arrModules)) {
            $this->blnError = true;

            $objTemplate = new \Isotope\Template('mod_message');
            $objTemplate->class = 'payment_method';
            $objTemplate->hl = 'h2';
            $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['payment_method'];
            $objTemplate->type = 'error';
            $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['noPaymentModules'];

            return $objTemplate->parse();
        }

        $objTemplate = new \Isotope\Template('iso_checkout_payment_method');

        if (!Isotope::getCart()->hasPayment() && !strlen($_SESSION['CHECKOUT_DATA']['payment']['module']) && count($arrModules) == 1) {

            Isotope::getCart()->Payment = $objLastModule;
            $_SESSION['CHECKOUT_DATA']['payment']['module'] = Isotope::getCart()->Payment->id;
            $arrModules[0]['checked'] = ' checked="checked"';

        } elseif (!Isotope::getCart()->hasPayment()) {

            if (\Input::post('FORM_SUBMIT') != '') {
                $objTemplate->error = $GLOBALS['TL_LANG']['MSC']['payment_method_missing'];
            }

            $this->blnError = true;
        }

        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['payment_method'];
        $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['payment_method_message'];
        $objTemplate->paymentMethods = $arrModules;

        if (!$this->hasError()) {
            $objPayment = Isotope::getCart()->getPaymentMethod();
            $this->objModule->arrOrderData['payment_method_id']    = $objPayment->id;
            $this->objModule->arrOrderData['payment_method']       = $objPayment->label;
            $this->objModule->arrOrderData['payment_note']         = $objPayment->note;
            $this->objModule->arrOrderData['payment_note_text']    = strip_tags($objPayment->note);
        }

        return $objTemplate->parse();
    }


    public function review()
    {
        return array(
            'payment_method' => array(
                'headline'    => $GLOBALS['TL_LANG']['MSC']['payment_method'],
                'info'        => Isotope::getCart()->Payment->checkoutReview(),
                'note'        => Isotope::getCart()->Payment->note,
                'edit'        => $this->addToUrl('step=payment', true),
            ),
        );
    }
}
